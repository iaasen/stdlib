<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 08.03.2016
 * Time: 15:27
 */

namespace Oppned;

///use DateTime;
use Exception;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionObject;
use ReflectionProperty;

/**
 * Class AbstractEntity
 * @package Oppned
 *
 * Intended to be the simplest possible object with possibility for type casting and setters.
 * Intended to be used with REST services where an object with public attributes are expected
 * Properties should be defined as public
 * Setter functionality only available through __construct and exchangeArray
 *
 */
class AbstractEntity
{
	/** @var DocBlockFactory  */
	private $docBlockFactory;

	public function __construct($data = [])
	{
		$this->docBlockFactory = DocBlockFactory::createInstance();
		if(count($data)) $this->exchangeArray($data);
	}

	public function exchangeArray($data) {
		foreach($data AS $key => $value) {
			// Look for setter method (setField())
			$setterName = 'set' . ucfirst($key);
			if(method_exists($this, $setterName)) {
				$this->$setterName($value);
				continue;
			}

			// All properties must be predefined
			if(!property_exists($this, $key)) throw new Exception("Property '$key' not found in " . get_class($this), 400);

			// Populate according to @var doc-comment
			$reflection = new ReflectionProperty($this, $key);
			//$factory  = DocBlockFactory::createInstance();
			$annotation = $this->docBlockFactory->create($reflection->getDocComment());
			if(!$annotation->hasTag('var')) throw new \LogicException("Property '$key' must have a @var tag in " . get_class($this), 500);

			switch($annotation->getTagsByName('var')[0]->getType()) {
				case 'bool':
					$this->$key = (bool) $value;
					break;
				case 'int':
					$this->$key = strlen($value) ? (int) $value : null;
					break;
				case 'float':
					$this->$key = (float) $value;
					break;
				case 'string':
					$this->$key = (string) $value;
					break;
				case 'string[]':
					if(is_string($value)) {
						if(in_array(substr($value, 0, 1), ['{', '['])) $this->$key = json_decode($value);
					}
					else $this->$key = $value;
					break;
//				case '\DateTime':
//					if(is_null($value)) $this->$key = null;
//					elseif(is_string($value)) $this->$key = new DateTime($value);
//					elseif($value instanceof DateTime) $this->$key = $value;
//					else throw new InvalidArgumentException("Property '$key' must be a string or an instance of \\DateTime in " . get_class($this));
//					break;
				default:
					if(is_null($this->$key) || !is_null($value)) { // Make sure default values are not overwritten with null
						$this->$key = $value;
					}
					break;
			}
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
}