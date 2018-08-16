<?php

namespace CrixuAMG\Decorators\Caches;

/**
 * Class CacheKey
 * @package CrixuAMG\Decorators\Caches
 */
class CacheKey
{
    /**
     * @param mixed ...$data
     *
     * @return string
     */
    public static function generate(...$data)
    {
        $format = '';
        $parameters = [];

        foreach ($data as $value) {
            if (self::valueCanBeStringified($value)) {
                if ($format) {
                    $format .= '.';
                }

                // Update the format
                $format .= self::getCacheKeyType($value);

                // Add it to the parameters
                $parameters[] = $value;
            }
        }

        return self::fromFormat($format, $parameters);
    }

    /**
     * @param string $format
     * @param array  $parameters
     *
     * @return string
     */
    public static function fromFormat(string $format, array $parameters)
    {
        $requestExtension = self::getDataFromRequest();

        if ($requestExtension) {
            $format .= '.%s';
            $parameters[] = $requestExtension;
        }

        return md5(vsprintf($format, $parameters));
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected static function getCacheKeyType($value): string
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

    /**
     * @param $value
     *
     * @return bool
     */
    private static function valueCanBeStringified($value): bool
    {
        return
            (
            !is_array($value)
            ) &&
            (
                (
                    !is_object($value) && settype($value, 'string') !== false
                ) ||
                (
                    is_object($value) && method_exists($value, '__toString')
                )
            );
    }

    /**
     * @return string
     */
    private static function getDataFromRequest()
    {
        $data = request()->only((array)config('decorators.cache.request_parameters'));
        $string = '';

        foreach ($data as $name => $value) {
            $string .= sprintf('%s.%s', $name, $value);
        }

        return $string;
    }
}
