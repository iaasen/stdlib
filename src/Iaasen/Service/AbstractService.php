<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 05.05.2018
 * Time: 17:30
 */

namespace Iaasen\Service;


use Iaasen\Service\ObjectKeyMatrix;

class AbstractService
{
	/**
	 * Returns an array where they key is the they keys from the objects and the value is an array of all objects with that key.
	 * Useful for collecting dependencies from the database
	 * @param array $objects
	 * @param string $key Element in the objects to index
	 * @return array Objects grouped by $key. ($key is the array key, array of objects is the array value)
	 * @deprecated It's a static function and better used in a static class usable from anywhere
	 */
	protected function getObjectKeyMatrix(array $objects, string $key = 'id') : array {
		return ObjectKeyMatrix::getObjectKeyMatrix($objects, $key);
	}

	/**
	 * @param array $objectKeyMatrix The object matrix given by getObjectKeyMatrix()
	 * @param string $objectFunction What function to call on parent object to add child object
	 * @param array $childObjects The child objects to populate
	 * @param string $childKey The child object key used to match against the object matrix
	 * @deprecated It's a static function and better used in a static class usable from anywhere
	 */
	protected function populateObjectKeyMatrixWithFunctionCall(array $objectKeyMatrix, string $objectFunction, array $childObjects, string $childKey) : void {
		ObjectKeyMatrix::populateObjectKeyMatrixWithFunctionCall($objectKeyMatrix, $objectFunction, $childObjects, $childKey);
	}
}