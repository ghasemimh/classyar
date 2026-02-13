<?php
defined('CLASSYAR_APP') || die('Error: 404. page not found');

class Validator {
    public static function str($value, int $maxLen = 255): string {
        $value = trim((string)$value);
        if ($maxLen > 0 && mb_strlen($value) > $maxLen) {
            $value = mb_substr($value, 0, $maxLen);
        }
        return $value;
    }

    public static function int($value, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): ?int {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return null;
        }
        $intVal = (int)$value;
        if ($intVal < $min || $intVal > $max) {
            return null;
        }
        return $intVal;
    }

    public static function positiveInt($value): ?int {
        return self::int($value, 1);
    }

    public static function page($value, int $default = 1): int {
        $page = self::positiveInt($value);
        return $page ?? $default;
    }

    public static function perPage($value, int $default = 12, int $max = 100): int {
        $perPage = self::positiveInt($value);
        if ($perPage === null) {
            return $default;
        }
        return min($perPage, $max);
    }
}
