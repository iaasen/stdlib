<?php
/**
 * User: ingvar.aasen
 * Date: 2024-11-22
 */

namespace Iaasen;

class AddressAnalyzer
{

    public static function dismantleAddress(string $addressString): array
    {
        if(!strlen($addressString)) return [];

        // Before comma includes street name and street number, names of places
        // After comma includes postal code and postal place
        $before = $addressString;
        $after = [];
        if(str_contains($addressString, ',')) {
            $segments = explode(',', $addressString);
            $before = explode(' ', trim($segments[0]));
            $after = explode(' ', trim($segments[1]));
        }
        else {
            $before = explode(' ', trim($addressString));
        }

        if(is_string($before)) $before = [$before];
        // Make a sql wildcard for veg/vei
        $before = array_map(function($segment) {
            return str_replace(['veg', 'vei'], 've_', $segment);
        }, $before);

        $words = [];
        $fields = [];

        // Extract different field types
        foreach($before AS $segment) {
            $matches = null;
            if(is_numeric($segment)) {
                $fields['number'] = $segment;
                $fields['letter'] = '';
            }
            elseif(preg_match('/^(\d+).*?([a-zA-Z]+)$/', $segment, $matches)) {
                $fields['number'] = $matches[1];
                $fields['letter'] = $matches[2];
            }
            else $words[] = $segment;
        }
        if(count($after)) {
            foreach($after AS $segment) {
                if(preg_match('/^\d{4}$/', $segment)) $fields['postal_code'] = $segment;
                elseif(ctype_alpha($segment)) $fields['postal_place'] = $segment;
            }
        }

        // Lowercase everything
        $words = array_map(function($segment) {
            return strtolower($segment);
        }, $words);
        $fields = array_map(function($segment) {
            return strtolower($segment);
        }, $fields);
        $fields['words'] = $words;

        return $fields;
    }

}
