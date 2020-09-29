<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 08.03.2016
 * Time: 15:27
 */

namespace Iaasen\Model;

use \Iaasen\DateTime;
use Exception;
use Iaasen\Exception\InvalidArgumentException;
use Laminas\Stdlib\ArraySerializableInterface;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractEntityV2
 * @package Iaasen
 *
 * Intended to be the simplest possible object with possibility for type casting and setters.
 * Intended to be used with REST services. The API server vil get properties from getArrayCopy()
 * Properties should be defined as protected to ensure automatic setters and getters.
 *
 */
class AbstractEntityV2 implements ModelInterfaceV2, ArraySerializableInterface
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
			$publicProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC + ReflectionProperty::IS_PROTECTED);

			foreach($publicProperties AS $property) {
				if($property->isStatic()) continue;

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

	/**
	 * We don't include static variables. Should we?
	 * @return array
	 */
	public function getArrayCopy() {
		$doc = self::$docBlockData[get_class($this)];

		$data = [];
		foreach($doc AS $key => $property) {
			$value = $this->__get($key);
			if(is_null($value)) $data[$key] = $value;
			elseif($property['type'] == 'objectArray') {
				foreach($value AS $rowNumber => $row) {
					$data[$key][$rowNumber] = $row->getArrayCopy();
				}
			}
			elseif($property['type'] == 'object') {
				$data[$key] = $value->getArrayCopy();
			}
			else {
				$data[$key] = $value;
			}
		}
		return $data;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
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
		// Look for setter method (setField())
		$setterName = 'set' . ucfirst($name);
		if(method_exists($this, $setterName)) {
			$this->$setterName($value);
			return;
		}

		$doc = self::$docBlockData[get_class($this)];
		if(isset($doc[$name])) $doc = $doc[$name];
		else {
			if($this->throwExceptionOnMissingProperty) throw new InvalidArgumentException("Property '$name' not found in " . get_class($this), 400);
			else return;
		}

		if($doc['type'] == 'objectArray') {
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
					if(is_object($value)) $this->$name = (array) $value;
					elseif(is_string($value)) {
						if(in_array(substr($value, 0, 1), ['{', '['])) $this->$name = (array) json_decode($value);
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
		if(isset($value->_class)) $className = $value->_class;

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
				if(isset($value->_class)) $className = $value->_class;
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
		$doc = self::$docBlockData[get_class($this)];
		$data = $this->getArrayCopy();
		unset($data['throwExceptionOnMissingProperty']);

		foreach($doc AS $key => $property) {
			if($property['type'] == 'primitive') {
				switch($property['value']) {
					case 'bool':
						$data[$key] = ($data[$key]) ? 1 : 0;
						break;
					case '\DateTime':
					case 'DateTime';
						/** @var DateTime $value */
						if($value) $data[$key] = $value->format('Y-m-d H:i:s');
						else $data[$key] = null;
						break;
				}
			}
		}
		return $data;


//		$reflection = new ReflectionClass($this);
//		$properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
//		$data = [];
//		foreach($properties AS $property) {
//			if(!$property->isStatic()) {
//				$name = $property->getName();
//				$value = $this->__get($name);
//				$factory  = DocBlockFactory::createInstance();
//				$annotation = $factory->create($property->getDocComment());
//				/** @var \phpDocumentor\Reflection\DocBlock\Tags\Var_ $tag */
//				$tag = $annotation->getTagsByName('var')[0];
//				switch($tag->getType()) {
//					case 'bool':
//						$data[$name] = ($value) ? 1 : 0;
//						break;
//					case '\DateTime':
//					case 'DateTime';
//						/** @var DateTime $value */
//						if($value) $data[$name] = $value->format('Y-m-d H:i:s');
//						else $data[$name] = null;
//						break;
//					default:
//						$data[$name] = $value;
//						break;
//				}
//			}
//		}
//		return $data;
	}
}