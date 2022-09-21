<?php

namespace App\Utility\Algorithms;

class StringAlgorithm
{
    public static function firstPatternMatching($string, $pattern): bool
    {
        $len = strlen($string) - strlen($pattern);
        $p_len = strlen($pattern);
        for ($i = 0; $i <= $len; $i++) {
            $counter = 0;
            for ($j = $i; $j < $i+$p_len; $j++) {
                if ($string[$j] == $pattern[$counter]) {
                    $counter++;
                    if ($counter == $p_len) {
                        return true;
                    }
                    continue;
                } else {
                    break;
                }
            }
        }
        return false;
    }

    /**
    * @param string $string string
    * @param string $string startString
    * @return boolean
    */
    public static function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    /**
     * @param string $string string
     * @param string $string startString
     * @return boolean
     */
    public static function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }
}
