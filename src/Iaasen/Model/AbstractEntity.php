<?php
/**
 * User: ingvar.aasen
 * Date: 08.03.2016
 */

namespace Iaasen\Model;

use \Iaasen\DateTime;
use Exception;
use Iaasen\Exception\InvalidArgumentException;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractEntity
 * @deprecated Implement Iaasen\Model\AbstractEntityV2
 *
 * This is a copy of AbstractEntityV2 except some function signatures
 * getArrayCopy() is now recursive
 * Protected attributes are now possible
 * Setter functionality is also available on protected attributes
 * Classes extending AbstractEntity and AbstractEntityV2 kan now be linked to each other
 *
 * Original functionality:
 * Intended to be the simplest possible object with possibility for type casting and setters.
 * Intended to be used with REST services where an object with public attributes are expected
 * Properties should be defined as public
 * Setter functionality only available through __construct, __set and exchangeArray
 *
 */
class AbstractEntity implements ModelInterface
{
	protected static array $docBlockData;
	/** @var string  */
	const MYSQL_TIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * if set to true an Exception will be sent when trying to set a property that is not defined
	 */
	protected bool $throwExceptionOnMissingProperty = false;

	/**
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
					$doc = ['nullable' => false];
					if($type instanceof Nullable) $doc['nullable'] = true;

					// Array of objects
					if($type instanceof Array_) {
						$type = $type->getValueType();
						if($type instanceof Nullable) {
							$doc['nullable'] = true;
							$type = $type->getActualType();
						}
						if($type instanceof Object_) {
							$doc['type'] = 'objectArray';
							$doc['value'] = $type->__toString();
						}
						else {
							$doc['type'] = 'array';
							$doc['value'] = 'mixed';
						}
					}
					// Object
					elseif($type instanceof Object_) {
						$doc['type'] = $type->__toString() == '\stdClass' ? 'stdClass' : 'object';
						$doc['value'] = $type->__toString();
					}
					// Primitive type
					else {
						$doc['type'] = 'primitive';
						$doc['value'] = $type->__toString();
					}
					// Save to static
					self::$docBlockData[$class][$property->name] = $doc;
				}


				// Get by property type
				else {
					$reflection = new ReflectionProperty(get_class($this), $property->name);
					if(!$reflection->hasType()) throw new \LogicException("Property '$property->name' must have a @var tag or property type declaration in " . get_class($this), 500);

					$reflection = $reflection->getType();
					if($reflection->getName() == 'array') $doc = [
						'type' => 'array',
						'value' => 'mixed',
					];
					elseif($reflection->getName() == 'object') $doc = [
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
					$doc['nullable'] = $reflection->allowsNull();
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


	/**
	 * Called when object is created from database by TableGateway
	 * Called when form is validated
	 */
	public function exchangeArray($data) {
		foreach($data AS $key => $value) {
			$this->__set($key, $value);
		}
	}

	/**
	 * Called by \Laminas\Form::bind()
	 * We don't include static variables. Should we?
	 * @return array
	 */
	public function getArrayCopy() {
		$doc = $this->getDocBlock();

		$data = [];
		foreach($doc AS $key => $property) {
			if($this->isInitialized($key)) {
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
			elseif($property['nullable']) {
				try {
					if(is_null($this->$key)) $data[$key] = null;
				}
				catch(\Error $e) {}
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
		if(method_exists($this, $getterName)) return $this->$getterName();
		// Property exists
		if(isset(self::$docBlockData[get_class($this)][$name])) return $this->$name;
		// Property missing
		if($this->throwExceptionOnMissingProperty)
			throw new InvalidArgumentException("Property '$name' not found or is private in " . get_class($this));
		else return null;
	}


	public function isInitialized(string $name) : bool {
		if(method_exists($this, 'get' . ucfirst($name))) return true;
		else return isset($this->$name);
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @throws Exception
	 */
	public function __set($name, $value) : void
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

		if(is_null($value) && $doc['nullable']) $this->$name = $value;
		elseif($doc['type'] == 'objectArray') {
			$this->setObjectArray($doc['value'], $name, $value);
		}
		elseif($doc['type'] == 'object') {
			$this->setObject($doc['value'], $name, $value);
		}
		elseif($doc['type'] == 'array') {
			if(is_object($value)) $this->$name = (array) $value;
			elseif(
				is_string($value) &&
				in_array(substr($value, 0, 1), ['{', '['])
			) {
				$this->$name = (array) json_decode($value);
			}
			else $this->$name = $value;
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
			case '\Iaasen\DateTime':
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

	public function __isset($name) : bool
	{
		if(!property_exists($this, $name)) return false;
		$doc = $this->getDocBlock();
		if(isset($doc[$name])) return isset($this->$name); // Why go by $doc, isn't it enough to check the property?
		return false; // Anything else is not set
	}


	public function __unset($name) : void
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

	public function databaseSaveArray()
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
			elseif(in_array($property['value'], ['\DateTime', 'DateTime', '\Iaasen\DateTime'])) {
				if(isset($data[$key])) $data[$key] = $this->$key->format(self::MYSQL_TIME_FORMAT);
			}
		}
		return $data;
	}


	public function setThrowExceptionOnMissingProperty(bool $throw) {
		$this->throwExceptionOnMissingProperty = $throw;
	}

}