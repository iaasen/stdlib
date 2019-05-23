<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 11.11.2015
 * Time: 19.19
 */

namespace Iaasen\Model;

use ArrayObject;
use Exception;
use Iaasen\Entity\DateTime;
use Iaasen\Exception\InvalidArgumentException;
use Iaasen\Exception\LogicException;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractModel
 * @package Iaasen\Model
 * @property \DateTime $timestamp_created
 * @property \DateTime $timestamp_updated
 */
abstract class AbstractModel extends ArrayObject  implements ModelInterface
{
	/** @var array */
	protected static $docBlockData;
	/** @var DateTime */
	protected $timestamp_created;
	/** @var DateTime */
	protected $timestamp_updated;
	/** @var string  */
	const MYSQL_TIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * if set to true an Exception will be sent when trying to set a property that is not defined
	 * @var bool  */
	private $throwExceptionOnMissingProperty = true;



	public function __construct()
	{
		$this->generateDocBlockData();
		$this->timestamp_created = new DateTime();
		$this->timestamp_updated = $this->timestamp_created;
		parent::__construct();
	}

	private function generateDocBlockData() {
		if(!isset(self::$docBlockData[get_class($this)])) {
			$docBlockFactory = DocBlockFactory::createInstance();
			$reflection = new ReflectionClass($this);
			$publicProperties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);

			foreach($publicProperties AS $property) {
				if($property->isStatic()) continue;

				// Read docBlock
				$annotation = $docBlockFactory->create($property->getDocComment());
				if(!$annotation->hasTag('var')) throw new LogicException("Property '$property->name' must have a @var tag in " . get_class($this));
				/** @var \phpDocumentor\Reflection\DocBlock\Tags\Var_ $tag */
				$tag = $annotation->getTagsByName('var')[0];
				$type = $tag->getType();

				// Array of objects
				if($type instanceof Array_ && $type->getValueType() instanceof Object_) {
					$doc = [
						'type' => 'objectArray',
						'value' => $type->getValueType()->__toString(),
					];
				}
				// Object
				elseif($type instanceof Object_) {
					$doc = [
						'type' => 'object',
						'value' => $type->__toString(),
					];
				}
				// Primitive type
				else {
					$doc = [
						'type' => 'primitive',
						'value' => $type->__toString(),
					];
				}

				// Save to static
				self::$docBlockData[get_class($this)][$property->name] = $doc;
			}
		}
	}

	protected function getDocBlock(string $name) : ?array {
		return (isset(self::$docBlockData[get_class($this)][$name])) ? self::$docBlockData[get_class($this)][$name] : null;
	}


	public function __get($name) {
		$getterName = 'get' . ucfirst($name);
		if(method_exists($this, $getterName)) return $this->$getterName();

		$doc = self::$docBlockData[get_class($this)];
		if(isset($doc[$name])) return $this->$name;

		throw new InvalidArgumentException("Property '$name' not found or is private in " . get_class($this));
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @throws Exception
	 * @throws LogicException
	 * @throws InvalidArgumentException
	 * @return void
     */
	public function __set($name, $value)
	{
		// Look for setter method (setField())
		$setterName = 'set' . ucfirst($name);
		if (method_exists($this, $setterName)) {
			$this->$setterName($value);
			return;
		}

		$doc = self::$docBlockData[get_class($this)];
		if (isset($doc[$name])) $doc = $doc[$name];
		else {
			if ($this->throwExceptionOnMissingProperty) throw new InvalidArgumentException("Property '$name' not found or is private in " . get_class($this));
			else return;
		}

		if ($doc['type'] == 'objectArray') {
			$this->setObjectArray($doc['value'], $name, $value);
		} elseif ($doc['type'] == 'object') {
			$this->setObject($doc['value'], $name, $value);
		} else {
			switch ($doc['value']) {
				case 'bool':
					$this->$name = (!is_null($value)) ? (bool)$value : null;
					break;
				case 'int':
					$this->$name = strlen($value) ? (int)$value : null;
					break;
				case 'float':
					$this->$name = (float)$value;
					break;
				case 'string':
					$this->$name = (string)$value;
					break;
				case 'string[]':
				case 'int[]':
				case 'mixed[]':
				case 'array':
					if (is_string($value)) {
						if (in_array(substr($value, 0, 1), ['{', '['])) $this->$name = json_decode($value);
					}
					else $this->$name = $value;
					break;
				default:
					if (is_null($this->$name) || !is_null($value)) { // Make sure default values are not overwritten with null
						$this->$name = $value;
					}
					break;
			}
		}
	}

	protected function setObject($className, $name, $value) {
		switch($className) {
			case 'object':
				$this->$name = $value;
				break;
			case '\DateTime':
			case 'DateTime':
				if(is_null($value)) $this->$name = null;
				elseif(is_string($value)) $this->$name = (strlen($value)) ? new DateTime($value) : null;
				elseif($value instanceof \DateTime) $this->$name = new DateTime($value->format('c'));
				elseif($value instanceof DateTime) $this->$name = $value;
				else throw new InvalidArgumentException("Property '$name' must be a string or an instance of DateTime in " . get_class($this));
				break;
			default:
				if(is_null($value)) $this->$name = null;
				elseif(is_string($value) && in_array(substr($value, 0, 1), ['{', '['])) $this->$name = json_decode($value);
				else $this->$name = ($value instanceof $className) ? $value : new $className($value);
				break;
		}
	}

	protected function setObjectArray($className, $name, $value) {
		$this->$name = [];
		if(is_array($value)) {
			foreach($value AS $row) {
				switch($className) {
					case 'object':
						$this->$name[] = $row;
						break;
					default:
						$this->$name[] = ($row instanceof $className) ? $row : new $className($row);
						break;
				}
			}
		}
	}

	public function __isset($name)
	{
		$doc = self::$docBlockData[get_class($this)];
		if(isset($doc[$name])) return isset($this->$name);
		return false; // Anything else is not set
	}

	public function __unset($name)
	{
		$doc = self::$docBlockData[get_class($this)];
		if(isset($doc[$name])) $this->$name = null;
	}

	public function __clone()
	{
		$this->id = null;
		$this->timestamp_created = new DateTime();
		$this->timestamp_updated = $this->timestamp_created;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$data = get_class($this) . PHP_EOL;
		$data .= @rt($this->databaseSaveArray());
		return $data;
	}

	/**
	 * Called when object is created from database by TableGateway
	 * Called when form is validated
	 *
	 * @param array $data
	 * @throws \Exception
	 * @return void
	 */
	public function exchangeArray($data) {
		foreach($data AS $key => $value) {
			$this->__set($key, $value);
		}
	}

	/**
	 * Called by \Zend\Form::bind()
	 * @return array
	 */
	public function getArrayCopy() {
		$data = [];
		foreach(self::$docBlockData[get_class($this)] AS $name => $doc) {
			$value = $this->__get($name);

			switch($doc['value']) {
				case '\DateTime':
				case 'DateTime':
					if($value) {
						/** @var DateTime $value */
						$data[$name] = $value->format(self::MYSQL_TIME_FORMAT);
					}
					else $data[$name] = null;
					break;
				default:
					$data[$name] = $value;
					break;
			}
		}
		return $data;
	}


	/**
	 * Used by \Priceestimator\Model\DbTable to format model data for the database.
	 * @return array
	 */
	public function databaseSaveArray() {
		$data = [];
		foreach(self::$docBlockData[get_class($this)] AS $name => $doc) {
			$value = $this->__get($name);
			switch ($doc['value']) {
				case 'bool':
					$data[$name] = ($value) ? 1 : 0;
					break;
				case '\DateTime':
					/** @var \DateTime $value */
					if($value) $data[$name] = $value->format(self::MYSQL_TIME_FORMAT);
					else $data[$name] = null;
					break;
				case 'string[]':
				case 'int[]':
				case '[]':
				case 'mixed[]':
				case 'array':
					if(is_array($value)) $data[$name] = json_encode(array_values($value));
					else $data[$name] = json_encode($value);
					break;
				case '\stdClass':
					$data[$name] = json_encode($value);
					break;
				default:
					$data[$name] = $value;
					break;
			}
		}
		$data['timestamp_updated'] = date(self::MYSQL_TIME_FORMAT);
		return $data;
	}

	public function setThrowExceptionOnMissingProperty(bool $throw) {
		$this->throwExceptionOnMissingProperty = $throw;
	}
}