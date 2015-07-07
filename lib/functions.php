<?php
/**
 * Utility  functions.
 *
 * User: boldhedgehog
 * Date: 05.12.12
 * Time: 22:03
 */

function hashKey($value)
{
    if (is_array($value)) {
        $value = implode('|', $value);
    }

    return hash('crc32', $value);
}

if (!function_exists('_')) {
    function _($string) {
        return $string;
    }
}
