<?php

namespace App\Libraries;

/**
 * Helper class for strings
 *
 * @category Libraries
 * @package  Helper
 */
class Str
{
    /**
     * Convert snake case to camel case
     *
     * @param string $input     string to convert
     * @param string $separator What is the separator
     *
     * @return bool
     */
    public static function camel($input, $separator = '_')
    {
        //replace separator with '' (empty space) and Capitalize.
        return str_replace($separator, '', ucwords($input, $separator));
    }

    /**
     * Convert camel case to snake case
     *
     * @param string $string string to convert
     *
     * @return bool
     */
    public static function snake($string)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }
}
