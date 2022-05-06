<?php
/**
 * User: ingvar
 * Date: 03.12.2021
 * Time: 10.53
 */

namespace Iaasen\Service;

class ObjectKeyMatrix
{
	/**
	 * Returns an array where they key is the they keys from the objects and the value is an array of all objects with that key.
	 * Useful for collecting dependencies from the database
	 * @param array $objects
	 * @param string $key Element in the objects to index by
	 * @return array Objects grouped by $key. ($key is the array key, array of objects is the array value)
	 */
	public static function getObjectKeyMatrix(array $objects, string $key = 'id') : array {
		return array_reduce($objects, function($carry, $item) use($key) {
			$carry[$item->$key][] = $item;
			return $carry;
		}, []);
	}


    public static function getArrayKeyMatrix(array $array, string $key = 'id') : array {
        return array_reduce($array, function($carry, $item) use($key) {
            $carry[$item[$key]][] = $item;
            return $carry;
        }, []);
    }


	/**
	 * @param array $objectKeyMatrix The object matrix given by getObjectKeyMatrix()
	 * @param string $objectFunction What function to call on parent object to add child object
	 * @param array $childObjects The child objects to populate
	 * @param string $childKey The child object key used to match against the object matrix
	 */
	public static function populateObjectKeyMatrixWithFunctionCall(array $objectKeyMatrix, string $objectFunction, array $childObjects, string $childKey) : void {
		foreach($childObjects AS $childObject) {
			if(isset($objectKeyMatrix[$childObject->$childKey])) {
				foreach($objectKeyMatrix[$childObject->$childKey] AS $object) {
					$object->$objectFunction($childObject);
				}
			}
		}
	}


	/**
	 * @param array $objectKeyMatrix The object matrix given by getObjectKeyMatrix()
	 * @param string $attribute The attribute to populate to in the parent object
	 * @param array $childObjects The child objects to populate
	 * @param string $childKey The child object key used to match against the object matrix
	 */
	public static function populateObjectKeyMatrixWithAttribute(array $objectKeyMatrix, string $attribute, array $childObjects, string $childKey) : void {
		foreach($childObjects AS $childObject) {
			if(isset($objectKeyMatrix[$childObject->$childKey])) {
				foreach($objectKeyMatrix[$childObject->$childKey] AS $object) {
					$object->$attribute = $childObject;
				}
			}
		}
	}
}