<?php

namespace App\GetVideo;

class Utils
{
    public static function determine_extension($text, $default = 'unknown'): string
    {
        $e = explode('.', $text);
        $last = end($e);
        preg_match('/^([a-zA-Z0-9]+)$/i', $last, $find);
        if (isset($find[1])) {
            return $find[1];
        }

        return $default;
    }

    public static function str_to_bool($s): bool
    {
        if ($s == 'none') {
            return false;
        } else if ($s == '0') {
            return false;
        } else if ($s == 'false') {
            return false;
        } else if ($s == 'true') {
            return true;
        } else if ($s == '1') {
            return true;
        } else if ($s !== false || $s !== null) {
            return true;
        }
    }
}
