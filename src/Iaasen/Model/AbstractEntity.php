<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 08.03.2016
 * Time: 15:27
 */

namespace Iaasen\Model;

use \Iaasen\Entity\DateTime;
use Exception;
use Iaasen\Exception\InvalidArgumentException;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

/**
 * Class AbstractEntity
 * @package Oppned
 *
 * Intended to be the simplest possible object with possibility for type casting and setters.
 * Intended to be used with REST services where an object with public attributes are expected
 * Properties should be defined as public
 * Setter functionality only available through __construct, __set and exchangeArray
 *
 */
class AbstractEntity implements \Iaasen\Model\ModelInterface
{
	/** @var array */
	private static $docBlockData;
	/**
	 * if set to true an Exception will be sent when trying to set a property that is not defined
	 * @var bool  */
	protected $throwExceptionOnMissingProperty = false;

	/**
	 * AbstractEntity constructor.
	 * @param array $data
	 */
	public function __construct($data = [])
	{
		$this->generateDocBlockData();
		if($data instanceof \stdClass) $data = (array) $data;
		if(!is_null($data) && count($data)) $this->exchangeArray($data);
	}

	private function generateDocBlockData() {
		if(!isset(self::$docBlockData[get_class($this)])) {


			$docBlockFactory = DocBlockFactory::createInstance();
			$reflection = new ReflectionClass($this);
			$publicProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

			foreach($publicProperties AS $property) {
				if($property->isStatic()) continue;

				// Setter
				$setterName = 'set' . ucfirst($property->name);
				if(method_exists($this, $setterName)) {
					$doc = [
						'type' => 'setter',
						'value' => $setterName,
					];
				}
				else {
					// Read docBlock
					$annotation = $docBlockFactory->create($property->getDocComment());
					if(!$annotation->hasTag('var')) throw new \LogicException("Property '$property->name' must have a @var tag in " . get_class($this), 500);
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
				}

				// Save to static
				self::$docBlockData[get_class($this)][$property->name] = $doc;
			}
		}
	}

	public function exchangeArray($data) {
		foreach($data AS $key => $value) {
			$this->__set($key, $value);
		}
	}

	public function getArrayCopy() {
		$reflection = new ReflectionObject($this);
		$properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

		$data = [];
		foreach($properties AS $property) {
			$propertyName = $property->name;
			$data[$propertyName] = $this->$propertyName;
		}
		return $data;
	}

	public function __get($name)
	{
		// Look for getter method (getField())
		$getterName = 'get' . ucfirst($name);
		if(method_exists($this, $getterName)) {
			return $this->$getterName();
		}
		// Default behaviour
		return $this->$name;
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @throws Exception
	 */
	public function __set($name, $value)
	{
		$doc = self::$docBlockData[get_class($this)];
		if(isset($doc[$name])) $doc = $doc[$name];
		else {
			if($this->throwExceptionOnMissingProperty) throw new InvalidArgumentException("Property '$name' not found in " . get_class($this), 400);
			else return;
		}

		if($doc['type'] == 'setter') {
			$setterName = $doc['value'];
			$this->$setterName($value);
		}
		elseif($doc['type'] == 'objectArray') {
			$this->setObjectArray($doc['value'], $name, $value);
		}
		elseif($doc['type'] == 'object') {
			$this->setObject($doc['value'], $name, $value);
		}
		else {
			switch($doc['value']) {
				case 'bool':
					$this->$name = (!is_null($value)) ? (bool) $value : null;
					break;
				case 'int':
					$this->$name = strlen($value) ? (int) $value : null;
					break;
				case 'float':
					$this->$name = (float) $value;
					break;
				case 'string':
					$this->$name = (string) $value;
					break;
				case 'string[]':
					if(is_string($value)) {
						if(in_array(substr($value, 0, 1), ['{', '['])) $this->$name = json_decode($value);
					}
					else $this->$name = $value;
					break;
				default:
					if(is_null($this->$name) || !is_null($value)) { // Make sure default values are not overwritten with null
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
				else throw new \InvalidArgumentException("Property '$name' must be a string or an instance of \\DateTime in " . get_class($this));
				break;
			default:
				if(is_null($value)) $this->$name = null;
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

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		if(!property_exists($this, $name)) return false;
		$reflection = new ReflectionProperty($this, $name);
		if(!$reflection->isPublic()) return false;
		return true;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function __unset($name)
	{
		if(property_exists($this, $name)) {
			$reflection = new ReflectionProperty($this, $name);
			if($reflection->isPublic()) unset($this->$name);
		}
	}

	/**
	 * @return void
	 */
	public function __clone()
	{
	}

	public function __toString()
	{
		$data = get_class($this) . PHP_EOL;
		$data .= @rt($this->databaseSaveArray());
		return $data;
	}

	public function databaseSaveArray()
	{
		$reflection = new ReflectionClass($this);
		$properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
		$data = [];
		foreach($properties AS $property) {
			if(!$property->isStatic()) {
				$name = $property->getName();
				$value = $this->__get($name);
				$factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
				$annotation = $factory->create($property->getDocComment());
				/** @var \phpDocumentor\Reflection\DocBlock\Tags\Var_ $tag */
				$tag = $annotation->getTagsByName('var')[0];
				switch($tag->getType()) {
					case 'bool':
						$data[$name] = ($value) ? 1 : 0;
						break;
					case '\DateTime':
					case 'DateTime';
						/** @var DateTime $value */
						if($value) $data[$name] = $value->format('Y-m-d H:i:s');
						else $data[$name] = null;
						break;
					default:
						$data[$name] = $value;
						break;
				}
			}
		}
		return $data;
	}
}