<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 11.11.2015
 * Time: 19.19
 */

namespace Iaasen\Model;

use DateTime;
use Exception;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractModel
 * @package Oppned
 * @property \DateTime $timestamp_created
 * @property \DateTime $timestamp_updated
 */
abstract class AbstractModel extends \ArrayObject  implements ModelInterface
{
	/** @var DateTime */
	protected $timestamp_created;
	/** @var DateTime */
	protected $timestamp_updated;

	const MYSQL_TIME_FORMAT = 'Y-m-d H:i:s';

	//abstract public function __clone();

	public function __construct()
	{
		$this->timestamp_created = new DateTime();
		$this->timestamp_updated = $this->timestamp_created;
	}

	public function __get($name) {
		if(property_exists($this, $name)) {
			$reflection = new ReflectionProperty($this, $name);
			if($reflection->isProtected()) return $this->$name;
			else throw new \Exception("Property '$name' in " . get_class($this) . " is private", 107);
		}
		throw new \Exception("Property '$name' not found in " . get_class($this), 106);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @throws Exception
	 * @throws LogicException
	 * @throws InvalidArgumentException
	 * @return void
     */
	public function __set($name, $value) {
		if(!property_exists($this, $name)) throw new Exception("Property '$name' not found in " . get_class($this), 106);
		$reflection = new ReflectionProperty($this, $name);
		$factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
		$annotation = $factory->create($reflection->getDocComment());
		if(!$annotation->hasTag('var')) throw new \LogicException("Property '$name' must have a @var tag in " . get_class($this));
		/** @var \phpDocumentor\Reflection\DocBlock\Tags\Var_ $tag */
		$tag = $annotation->getTagsByName('var')[0];
		switch($tag->getType()) {
			case 'bool':
				$this->$name = (bool) $value;
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
			case 'int[]':
				if(is_string($value)) {
					if(in_array(substr($value, 0, 1), ['{', '['])) $this->$name = json_decode($value);
				}
				else $this->$name = $value;
				break;
			case '\DateTime':
				if(is_null($value)) $this->$name = null;
				elseif(is_string($value)) $this->$name = new DateTime($value);
				elseif($value instanceof DateTime) $this->$name = $value;
				else throw new InvalidArgumentException("Property '$name' must be a string or an instance of \\DateTime in " . get_class($this));
				break;
			default:
				$this->$name = $value;
				break;
		}
	}

	public function __isset($name)
	{
		if(!property_exists($this, $name)) throw new Exception("Property '$name' not found in " . get_class($this), 106);
		$reflection = new ReflectionProperty($this, $name);
		if($reflection->isProtected()) return isset($this->$name);
		else throw new Exception("Property '$name' in " . get_class($this) . " is private", 107);
	}

	public function __unset($name)
	{
		if(property_exists($this, $name)) {
			$reflection = new ReflectionProperty($this, $name);
			if($reflection->isProtected()) {
				unset($this->$name);
			}
		}
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
		$reflection = new ReflectionClass($this);
		$properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
		$data = [];
		foreach($properties AS $property) {
			$name = $property->getName();
			$value = $this->__get($name);
			$factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
			$annotation = $factory->create($property->getDocComment());

			/** @var \phpDocumentor\Reflection\DocBlock\Tags\Var_ $tag */
			$tag = $annotation->getTagsByName('var')[0];
			switch($tag->getType()) {
				case '\DateTime':
					if($value) {
						/** @var \DateTime $value */
						$data[$name] = $value->format(self::MYSQL_TIME_FORMAT);
//						if(strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) // Chrome
//							$data[$name] = $value->format('Y-m-d H:i');
//						else
//							$data[$name] = $value->format('d.m.Y H:i');
					}
					else $_data[$name] = null;
					break;
				default:
					$data[$name] = $value;
					break;
			}
		}
		return $data;
	}


	/**
	 * Used by \Priceestimator\Model\DbTable to format modeldata for the database.
	 * @return array
	 */
	public function databaseSaveArray() {
		$reflection = new ReflectionClass($this);
		$properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
		$data = [];
		foreach($properties AS $property) {
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
					/** @var \DateTime $value */
					if($value) $data[$name] = $value->format(self::MYSQL_TIME_FORMAT);
					else $data[$name] = null;
					break;
				case 'string[]':
				case 'int[]':
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
}