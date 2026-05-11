<?php
/**
 * User: ingvar.aasen
 * Date: 24.06.2022
 */

namespace Iaasen;

use Iaasen\Exception\InvalidArgumentException;

class WithOptions {

	/**
     * Current accepted formats:
     * null - returns $default
     * array with value * or 'all' in top level - returns $full
     * array - returned as it is
     * empty string - returns []
     * GraphQl Selection Set - parsed by parseGraphQlSelectionSet())
     * JSON - converted to PHP array and returned as it is
     * comma separated list - Converted request for populated objects on top level
     * The string "all" - returns $full
     *
     * Legacy formats:
     *
	 * @param array|string|null $withString
	 * @param array $default
	 * @return array
	 */
	public static function extractWith(array|string|null $withString, array $default = [], array $full = []) : array {
		// Return default
		if(is_null($withString)) return $default;

		// Is already an array
		if(is_array($withString)) {
			if(in_array('all', $withString)) return $full;
			if(in_array('*', $withString)) return $full;
            return $withString;
		}

        $withString = trim($withString);

		// Is an empty string
		if(!strlen($withString)) return [];

        // Is GraphQL Selection Set
        if (static::isValidGraphQlSelectionSet($withString)) {
            return static::parseGraphQlSelectionSet($withString);
        }

        // Is JSON
		if(preg_match('/^([{\[].*[}\]])$/', $withString)) {
			$with = json_decode($withString, 1);
			if(is_null($with)) throw new InvalidArgumentException("Invalid format of 'with' attribute (is this correct json?)");
			return (array) $with;
		}

        // Is a comma separated list
		$data = explode(',', $withString);

		// 'all' keyword
		if(in_array('all', $data)) return $full;

		// Convert to array
		$data = array_flip($data);
		$with = [];
		foreach($data AS $key => $value) $with[$key] = [];
		return $with;
	}


    /**
     * Recursively filter the $receivedWith and make sure it has only elements that also exists in the $referenceWith
     * The response will have the keys in the same order as the reference.
     * @param array $receivedWith Usually received from query
     * @param array $referenceWith Usually the WITH_OPTIONS from an entity
     * @return array
     */
    public static function filterWithArray(array $receivedWith, array $referenceWith): array
    {
        $result = [];

        foreach ($referenceWith as $key => $referenceValue) {
            // The key must exist in $receivedWith
            if (!array_key_exists($key, $receivedWith)) {
                continue;
            }

            // If the reference has substructure -> call recursively
            if (!empty($referenceValue) && is_array($receivedWith[$key])) {
                $result[$key] = self::filterWithArray($receivedWith[$key], $referenceValue);
            }
            // The leaf node in the filter always has an empty array
            else {
                $result[$key] = [];
            }
        }

        return $result;
    }

    public static function isValidGraphQlSelectionSet(string $input): bool
    {
        $input = trim($input);

        // Must start and end with {}
        if ($input === '' || $input[0] !== '{' || substr($input, -1) !== '}') {
            return false;
        }

        $pos = 1; // Skip first {
        $length = strlen($input);

        try {
            self::parseValidationLevel($input, $pos, $length);
            return $pos === $length; // Must have read the whole string
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function parseGraphQlSelectionSet(string $input): array
    {
        // Strip whitespace at beginning and end
        $input = trim($input);

        // Remove the outer {} braces
        if ($input[0] === '{') {
            $input = substr($input, 1, -1);
        }

        $pos = 0;
        return static::parseGraphQlSelectionSetRecursive($input, $pos);
    }

    private static function parseGraphQlSelectionSetRecursive(string $input, int &$pos): array
    {

        $result = [];
        $length = strlen($input);

        while ($pos < $length) {
            self::skipGraphQlSeparators($input, $pos);

            // End of this level
            if ($pos >= $length) break;
            if ($input[$pos] === '}') {
                $pos++;
                break;
            }

            // Read field names. Allowing !
            $name = '';
            while ($pos < $length && preg_match('/[A-Za-z0-9_!]/', $input[$pos])) {
                $name .= $input[$pos];
                $pos++;
            }

            self::skipGraphQlSeparators($input, $pos);

            // Nested?
            if ($pos < $length && $input[$pos] === '{') {
                $pos++; // {
                $result[$name] = self::parseGraphQlSelectionSetRecursive($input, $pos);
            } else {
                $result[] = $name;
            }
        }

        return self::applyBangRule($result);
    }
    
    private static function parseValidationLevel(string $input, int &$pos, int $length): void
    {
        while ($pos < $length) {

            self::skipGraphQlSeparators($input, $pos);

            if ($pos >= $length) return;

            // End of level
            if ($input[$pos] === '}') {
                $pos++;
                return;
            }

            // Must be the start of a field (! or letter)
            if (!preg_match('/[A-Za-z_!]/', $input[$pos])) {
                throw new \Exception("Invalid field start");
            }

            // Read field
            $name = '';

            // Optional !
            if ($input[$pos] === '!') {
                $name .= '!';
                $pos++;

                // ! must be followed by a name
                if ($pos >= $length || !preg_match('/[A-Za-z_]/', $input[$pos])) {
                    throw new \Exception("Invalid ! usage");
                }
            }

            // Read the rest of name
            while ($pos < $length && preg_match('/[A-Za-z0-9_]/', $input[$pos])) {
                $name .= $input[$pos];
                $pos++;
            }

            self::skipGraphQlSeparators($input, $pos);

            // Nested block?
            if ($pos < $length && $input[$pos] === '{') {
                $pos++; // Sskip {
                self::parseValidationLevel($input, $pos, $length);
            }
        }

        // Missing } at the end is an error
        throw new \Exception("Missing closing brace");
    }
    
    private static function skipGraphQlSeparators(string $input, int &$pos): void
    {
        $length = strlen($input);
        while (
            $pos < $length &&
            (
                $input[$pos] === ',' ||
                $input[$pos] === ' ' ||
                $input[$pos] === "\n" ||
                $input[$pos] === "\r" ||
                $input[$pos] === "\t"
            )
        ) {
            $pos++;
        }
    }

    private static function applyBangRule(array $result): array
    {
        $hasNonBang = false;
        // Look for regular field names. Ignore ! and nested structures
        foreach ($result as $key => $value) {
            if (is_int($key)) {
                if ($value[0] !== '!') {
                    $hasNonBang = true;
                    break;
                }
            }
        }

        // If at least one regular field is found, remove all fields with ! on this level
        if ($hasNonBang) {
            foreach ($result as $key => $value) {
                if (is_int($key) && $value[0] === '!') {
                    unset($result[$key]);
                }
            }

            // Re-index numeric array keys, keep assoc keys
            $numeric = [];
            $assoc = [];
            foreach ($result as $key => $value) {
                if (is_int($key)) {
                    $numeric[] = $value; // Implicitly reindexed
                } else {
                    $assoc[$key] = $value; // Keep assoc key
                }
            }
            // Merge numeric with assoc
            $result = $numeric + $assoc;

        }

        return $result;
    }

    public static function separateWithFromWithout(array $input): array
    {
        $with = [];
        $without = [];

        foreach ($input as $key => $value) {
            // Nested object, identified by the value being an array
            if(is_array($value)) {
                [$nestedWith, $nestedWithout] = self::separateWithFromWithout($value);
                $with[$key] = $nestedWith;
                if (!empty($nestedWithout)) {
                    $without[$key] = $nestedWithout;
                }
            }

            // Simple property
            else {
                if (str_starts_with($value, '!')) {
                    $without[] = substr($value, 1); // Strip the !
                } else {
                    $with[] = $value;
                }
            }

        }

        return [$with, $without];
    }

}
