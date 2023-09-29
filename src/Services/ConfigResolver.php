<?php

namespace CrixuAMG\Decorators\Services;

class ConfigResolver
{
    public static function get(string $key, $default = null, bool $fromConfiguration = false)
    {
        if ($fromConfiguration) {
            $key = sprintf("configuration.%s", $key);
        }

        return config(sprintf("decorators.%s", $key), $default);
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
                $output .= sprintf("%s'%s' => [%s", $indent, $key, PHP_EOL);
            } else if (is_string($key) && is_array($value)) {
                // Add a key and the corresponding array value
                $output .= sprintf("%s'%s' => [%s%s",
                    $indent,
                    $key,
                    PHP_EOL,
                    self::generateConfiguration($value, $depth + 1));
            } else if (is_string($key) && is_string($value)) {
                // Add string to string values
                $output .= sprintf("%s'%s' => %s::class,%s", $indent, $key, $value, PHP_EOL);
            } else if (is_int($key) && is_string($value)) {
                // Add only string values
                $output .= sprintf("%s %s::class,%s", $indent, $value, PHP_EOL);
                $addClosingBracket = false;
            }
        }

        if ($addClosingBracket) {
            $output .= sprintf("%s],%s", $indent, PHP_EOL);
        }

        return str_replace('/', "\\", $output);
    }
}
