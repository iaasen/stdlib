<?php
/**
 * User: ingvar.aasen
 * Date: 24.06.2022
 */

namespace Iaasen;

use Iaasen\Exception\InvalidArgumentException;

class WithOptions {

	/**
     * Accepted formats:
     * null - returns $default
     * array with value * or 'all' in top level - returns $full
     * array - returned as it is
     * empty string - returns []
     * GraphQl Selection Set - parsed by parseGraphQlSelectionSet())
     * JSON - converted to PHP array and returned as it is
     * comma separated list - Converted request for populated objects on top level
     * The string "all" - returns $full
	 */
	public static function extractWith(array|string|null $input, array $default = [], array $full = []) : array {
		// Return default
		if(is_null($input)) return $default;

		// Is already an array
		if(is_array($input)) return self::parseArray($input, $full);

        // Is a string
        $input = trim($input);

		// Is an empty string
		if($input === '') return [];

        // Is GraphQL Selection Set
        if (static::isValidGraphQlSelectionSet($input)) {
            return static::parseGraphQlSelectionSet($input);
        }

        // Is JSON
		if(self::isValidJson($input)) {
            return self::parseJson($input);
		}

        // Is a comma separated list
        if(self::isValidCommaSeparatedList($input)) {
            return self::parseCommaSeparatedList($input, $full);
        }

        throw new InvalidArgumentException('Invalid WITH format');
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

    public static function isValidJson(string $input): bool
    {
        try {
            json_decode($input, true, flags: JSON_THROW_ON_ERROR);
            return true;
        } catch (\JsonException) {
            return false;
        }
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

        if(self::isValidGraphQlSelectionSetRecursive($input, $pos, $length)) {
            return $pos === $length; // Must have read the whole string
        }
        else return false;
    }

    public static function isValidCommaSeparatedList(string $input): bool
    {
        $input = trim($input);
        if ($input === '') return false;
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\s*,\s*[a-zA-Z_][a-zA-Z0-9_]*)*$/', $input) === 1;
    }

    private static function parseArray(array $input, array $full): array
    {
        if(in_array('all', $input, true)) return $full;
        if(in_array('*', $input, true)) return $full;
        return $input;
    }

    private static function parseCommaSeparatedList(string $input, array $full): array
    {
        $data = array_map('trim', explode(',', $input));

        // Contains the 'all' keyword
        if(in_array('all', $data, true)) return $full;

        // Convert to array
        $data = array_flip($data);
        $with = [];
        foreach($data AS $key => $value) $with[$key] = [];
        return $with;

    }

    private static function parseJson(string $input): array
    {
        $with = json_decode($input, 1);
        if(is_null($with)) throw new InvalidArgumentException("Invalid format of 'with' attribute (is this correct json?)");
        return (array) $with;
    }

    public static function parseGraphQlSelectionSet(string $input): array
    {
        $tokens = self::tokenizeGraphQlSelectionSet($input);
        $pos = 0;
        return self::parseGraphQlSelectionSetRecursive($tokens, $pos);
    }

    /**
     * @param string[] $tokens
     */
    private static function parseGraphQlSelectionSetRecursive(array $tokens, int &$pos): array
    {
        $result = [];

        // expect {
        if (!isset($tokens[$pos]) || $tokens[$pos] !== '{') {
            throw new \InvalidArgumentException("Expected '{'");
        }
        $pos++;

        while (isset($tokens[$pos])) {
            $token = $tokens[$pos];

            // end of block
            if ($token === '}') {
                $pos++;
                break;
            }

            if ($token === ',') {
                $pos++;
                continue;
            }

            // field name
            $name = $token;
            $pos++;

            // nested?
            if (isset($tokens[$pos]) && $tokens[$pos] === '{') {
                $result[$name] = self::parseGraphQlSelectionSetRecursive($tokens, $pos);
            } else {
                $result[] = $name;
            }
        }
        return $result;

        return self::applyBangRule($result);

    }


    private static function tokenizeGraphQlSelectionSet(string $input): array
    {
        $tokens = [];
        $length = strlen($input);
        $i = 0;

        while ($i < $length) {
            $char = $input[$i];

            // Skip token separators
            if (ctype_space($char) || $char === ',') {
                $i++;
                continue;
            }

            // single char tokens
            if ($char === '{' || $char === '}') {
                $tokens[] = $char;
                $i++;
                continue;
            }

            // identifier
            if (ctype_alnum($char) || $char === '_' || $char === '!') {
                $start = $i;
                while (
                    $i < $length &&
                    (ctype_alnum($input[$i]) || $input[$i] === '_' || $input[$i] === '!')
                ) {
                    $i++;
                }

                $tokens[] = substr($input, $start, $i - $start);
                continue;
            }

            throw new \InvalidArgumentException("Invalid character '{$char}'");
        }

        return $tokens;
    }


    private static function isValidGraphQlSelectionSetRecursive(string $input, int &$pos, int $length): bool
    {
        while ($pos < $length) {
            self::skipGraphQlSeparators($input, $pos);

            if ($pos >= $length) return true;

            $char = $input[$pos];

            // End of level
            if ($char === '}') {
                $pos++;
                return true;
            }


            // Must be the start of a field (! or letter)
            if (!(ctype_alpha($char) || $char === '_' || $char === '!')) {
                return false;
            }

            // Optional !
            if ($char === '!') {
                $pos++;
                if ($pos >= $length) return false;
                $char = $input[$pos];

                // ! must be followed by a name
                if (!(ctype_alpha($char) || $char === '_')) return false;
            }

            // Read the rest of name
            while($pos < $length) {
                $char = $input[$pos];
                if(!(ctype_alnum($char) || $char === '_' || $char === '!')) break;
                $pos++;
            }

            self::skipGraphQlSeparators($input, $pos);

            // Nested block?
            if ($pos < $length && $input[$pos] === '{') {
                $pos++;
                if(!self::isValidGraphQlSelectionSetRecursive($input, $pos, $length)) return false;
            }
        }

        return false; // Missing closing brace "}"
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

    /**
     * And exclude rules beginning with ! will have no purpose if they are combined with include rules for simple fields.
     * This function will look for include rules and if found remove any exclude rules beginning with !.
     */
    private static function applyBangRule(array $result): array
    {
        $hasInclude = false;

        foreach ($result as $key => $value) {
            if (is_int($key) && $value[0] !== '!') {
                $hasInclude = true;
                break;
            }
        }

        if (!$hasInclude) {
            return $result;
        }

        return array_values(array_filter(
            $result,
            fn($value, $key) => !is_int($key) || $value[0] !== '!',
            ARRAY_FILTER_USE_BOTH
        ));
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
