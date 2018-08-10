<?php

namespace CrixuAMG\Decorators\Caches;

class CacheKey
{
    public static function generate(...$data)
    {
        dd($data);

        $format = '';
        $parameters = [];
    }

    public static function fromFormat(string $format, array $parameters)
    {
        return md5(vsprintf($format, $parameters));
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function getCacheKeyType($value): string
    {
        // Make sure to preserve float values
        if (\is_float($value)) {
            return '%f';
        }

        // Use it as an unsigned integer
        if (is_numeric($value)) {
            return '%u';
        }

        // Default fall back to string
        return '%s';
    }
}
