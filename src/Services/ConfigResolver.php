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
}
