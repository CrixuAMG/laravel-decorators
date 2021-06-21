<?php

namespace CrixuAMG\Decorators\Services;

class ConfigResolver
{
    public static function get(string $key, $default = null, bool $fromConfiguration = false)
    {
        if ($fromConfiguration) {
            $key = "configuration.$key";
        }

        return config("decorators.$key", $default);
    }

    public static function generateConfiguration(array $array, int $depth = 0): string
    {
        $output = '';
        $indent = '';
        $addClosingBracket = true;

        if ($depth > 0) {
            $indent = str_repeat("\t", $depth);
        }

        foreach ($array as $key => $value) {
            if (is_string($key) && !$value) {
                // Add start of array
                $output .= "$indent'$key' => [".PHP_EOL;
            } elseif (is_string($key) && is_array($value)) {
                // Add a key and the corresponding array value
                $output .= "$indent'$key' => [".PHP_EOL.self::generateConfiguration($value, $depth + 1);
            } elseif (is_string($key) && is_string($value)) {
                // Add string to string values
                $output .= "$indent'$key' => $value::class,".PHP_EOL;
            } elseif (is_int($key) && is_string($value)) {
                $value = str_replace('/', "\\", $value);

                // Add only string values
                $output .= "$indent $value::class,".PHP_EOL;
                $addClosingBracket = false;
            }
        }

        if ($addClosingBracket) {
            $output .= "$indent],".PHP_EOL;
        }

        return $output;
    }
}
