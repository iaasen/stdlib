<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 05.05.2018
 * Time: 17:30
 */

namespace Iaasen\Service;


class AbstractService
{
	/**
	 * Returns an array where they key is the they keys from the objects and the value is an array of all objects with that key.
	 * Useful for collecting dependencies from the database
	 * @param object[] $objects
	 * @param string $key
	 * @return object[]
	 */
	protected function getObjectKeyMatrix(array $objects, string $key = 'id') : array {
		return array_reduce($objects, function($carry, $item) use($key) {
			$carry[$item->$key][] = $item;
			return $carry;
		}, []);

//		//Does the same as the above code
//		$objectKeyMatrix = [];
//		foreach($objects AS $object) {
//			$objectKeyMatrix[$object->$key][] = $object;
//		}
//		return $objectKeyMatrix;
	}

	/**
	 * @param object[] $objectKeyMatrix The object matrix given by getObjectKeyMatrix()
	 * @param string $objectFunction What function to call on parent object
	 * @param object[] $childObjects The child objects to populate
	 * @param string $childKey The child object key used to match against the object matrix
	 */
	protected function populateObjectKeyMatrixWithFunctionCall(array $objectKeyMatrix, string $objectFunction, array $childObjects, string $childKey) : void {
		foreach($childObjects AS $childObject) {
			if(isset($objectKeyMatrix[$childObject->$childKey])) {
				foreach($objectKeyMatrix[$childObject->$childKey] AS $object) {
					$object->$objectFunction($childObject);
				}
			}
		}
	}
}