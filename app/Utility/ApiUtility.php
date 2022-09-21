<?php

namespace App\Utility;

class ApiUtility
{

    /**
     * String separator
     * @param string $string
     * @param array 
     */

    public static function filterSeparator($string): array
    {
        $parts = explode(',', $string);
        $filters = [];
        foreach ($parts as $part) {
            $second_part = explode(':', $part);
            $filters[] = [
                'key' =>  $second_part[0],
                'value' => $second_part[1]
            ];
        }
        return $filters;
    }

    public static function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public static function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }
}
