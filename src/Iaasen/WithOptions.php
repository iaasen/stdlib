<?php
/**
 * User: ingvar.aasen
 * Date: 24.06.2022
 */

namespace Iaasen;

use Iaasen\Exception\InvalidArgumentException;

class WithOptions {

	/**
	 * @param array|string|null $withString
	 * @param array $default
	 * @return array
	 */
	public static function extractWith($withString, array $default = []) : array {
		// Return default
		if(is_null($withString)) return $default;

		// Is already an array
		if(is_array($withString)) return $withString;

		// Is an empty string
		if(!strlen($withString)) return [];

		// Is JSON
		if(preg_match('/^([{\[].*[}\]])$/', $withString)) {
			$with = json_decode($withString, 1);
			if(is_null($with)) throw new InvalidArgumentException("Invalid format of 'with' attribute (is this correct json?)");
			return (array) $with;
		}

		// Is a comma separated list
		$data = explode(',', $withString);

		// 'all' keyword
		if(in_array('all', $data)) return $default;

		// Convert to array
		$data = array_flip($data);
		$with = [];
		foreach($data AS $key => $value) $with[$key] = [];
		return $with;
	}

}
