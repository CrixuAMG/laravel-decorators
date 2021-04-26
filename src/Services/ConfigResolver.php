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
}
