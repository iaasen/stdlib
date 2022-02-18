<?php
/**
 * User: ingvar.aasen
 * Date: 08.03.2016
 * Time: 15:27
 */

namespace Iaasen\Model;

use \Iaasen\DateTime;
use Exception;
use Iaasen\Exception\InvalidArgumentException;
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
class AbstractEntityV2 implements ModelInterfaceV2
{
	/** @var array */
	protected static $docBlockData;
	/** @var string  */
	const MYSQL_TIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * if set to true an Exception will be sent when trying to set a property that is not defined
	 * @var bool  */
	protected $throwExceptionOnMissingProperty = false;

	/**
	 * AbstractEntity constructor.
	 * @param array|\stdClass $data
	 */
	public function __construct($data = [])
	{
		$this->generateDocBlockData();
		if($data instanceof \stdClass) $data = (array) $data;
		if(!is_null($data) && count($data)) $this->exchangeArray($data);
	}


	public function __wakeup()
	{
		self::generateDocBlockData();
	}


	private function generateDocBlockData($class = null) {
		if(!$class) $class = get_class($this);
		if(!isset(self::$docBlockData[$class])) {
			$docBlockFactory = DocBlockFactory::createInstance();
			$reflection = new ReflectionClass($this);
			$publicProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC + ReflectionProperty::IS_PROTECTED);

			foreach($publicProperties AS $property) {
				if($property->isStatic()) continue;
				if($property->name == 'throwExceptionOnMissingProperty') continue;

				// Get by docBlock
				$annotation = $property->getDocComment();
				if($annotation) $annotation = $docBlockFactory->create($property->getDocComment());
				if($annotation && $annotation->hasTag('var')) {
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
							'type' => $type->__toString() == '\stdClass' ? 'stdClass' : 'object',
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
					self::$docBlockData[$class][$property->name] = $doc;
				}


				// Get by property type
				else {
					$reflection = new ReflectionProperty(get_class($this), $property->name);
					if(!$reflection->hasType()) throw new \LogicException("Property '$property->name' must have a @var tag or property type declaration in " . get_class($this), 500);

					$reflection = $reflection->getType();
//					if($reflection->getName() == 'object') throw new \LogicException("'object' property is not allowed", 500);
//					if($reflection->getName() == 'array') throw new \LogicException("'array' property must be specified in a @var comment", 500);

					if($reflection->getName() == 'object') $doc = [
						'type' => 'object',
						'value' => $reflection->getName(),
					];
					elseif($reflection->isBuiltin()) $doc = [
						'type' => 'primitive',
						'value' => $reflection->getName(),
					];
					else $doc = [
						'type' => 'object',
						'value' => '\\' . $reflection->getName(),
					];
					self::$docBlockData[$class][$property->name] = $doc;
				}
			}
		}
	}


	protected function getDocBlock(?string $class = null) : ?array {
		if(!$class) $class = get_class($this);
		if(!isset(self::$docBlockData[$class])) $this->generateDocBlockData();
		return self::$docBlockData[$class];

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
		$doc = $this->getDocBlock();

		$data = [];
		foreach($doc AS $key => $property) {
			$value = $this->__get($key);
			if(is_null($value)) $data[$key] = $value;
			elseif($property['type'] == 'objectArray') {
				if(count($value)) {
					foreach($value AS $rowNumber => $row) {
						$data[$key][$rowNumber] = $row->getArrayCopy();
					}
				}
				else $data[$key] = $value;
			}
			elseif($property['type'] == 'stdClass') {
				$data[$key] = (array) $value;
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
	public function __get(string $name)
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
	public function __set(string $name, $value) : void
	{
		// Look for setter method (setField())
		$setterName = 'set' . ucfirst($name);
		if(method_exists($this, $setterName)) {
			$this->$setterName($value);
			return;
		}

		$doc = $this->getDocBlock();
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
				case 'int[]':
				case 'mixed[]':
				case 'array':
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

	public function __isset(string $name) : bool
	{
		if(!property_exists($this, $name)) return false;
		$doc = $this->getDocBlock();
		if(isset($doc[$name])) return isset($this->$name); // Why go by $doc, isn't it enough to check the property?
		return false; // Anything else is not set
	}


	public function __unset(string $name) : void
	{
		$doc = $this->getDocBlock();
		if(isset($doc[$name])) $this->$name = null;
	}

	/**
	 * __clone() is run on the copied object when making a clone using the 'clone' keyword
	 * @return void
	 */
	public function __clone()
	{
	}


	public function __toString() : string
	{
		$data = get_class($this) . PHP_EOL;
		$data .= @rt($this->databaseSaveArray());
		return $data;
	}

	public function databaseSaveArray() : array
	{
		$doc = $this->getDocBlock();
		$data = $this->getArrayCopy();
		unset($data['throwExceptionOnMissingProperty']);

		foreach($doc AS $key => $property) {
			if($property['type'] == 'primitive') {
				switch($property['value']) {
					case 'bool':
						$data[$key] = ($data[$key]) ? 1 : 0;
						break;
				}
			}
			elseif($property['type'] == 'object') {
				switch($property['value']) {
					case '\DateTime':
					case 'DateTime':
						/** @var DateTime $value */
						if(isset($this->$key) && $this->$key) $data[$key] = $this->$key->format('Y-m-d H:i:s');
						else $data[$key] = null;
						break;
				}
			}
		}
		return $data;
	}


	public function setThrowExceptionOnMissingProperty(bool $throw) {
		$this->throwExceptionOnMissingProperty = $throw;
	}

}