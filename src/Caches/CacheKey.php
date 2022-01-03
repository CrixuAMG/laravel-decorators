<?php

namespace CrixuAMG\Decorators\Caches;

use CrixuAMG\Decorators\Exceptions\ValueCannotBeStringifiedException;

/**
 * Class CacheKey
 *
 * @package CrixuAMG\Decorators\Caches
 */
class CacheKey
{
    /**
     * @param  mixed  ...$data
     *
     * @return string
     * @throws ValueCannotBeStringifiedException
     */
    public static function generate(...$data): string
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

                continue;
            }

            throw new ValueCannotBeStringifiedException(
                __('Value cannot be stringified.')
            );
        }

        return self::fromFormat($format, $parameters);
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
     * @param $value
     *
     * @return string
     */
    public static function getCacheKeyType($value): string
    {
        // Use it as an unsigned integer
        if (is_numeric($value)) {
            return '%u';
        }

        // Default fall back to string
        return '%s';
    }

    /**
     * @param  string  $format
     * @param  array  $parameters
     *
     * @return string
     */
    public static function fromFormat(string $format, array $parameters): string
    {
        $requestExtension = self::getDataFromRequest();

        if ($requestExtension) {
            $format .= '.%s';
            $parameters[] = $requestExtension;
        }

        return md5(vsprintf($format, $parameters));
    }

    /**
     * @return string
     */
    private static function getDataFromRequest(): string
    {
        $data = request()->except((array) config('decorators.cache.request_cache_exceptions'));
        $string = '';

        foreach ($data as $name => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $string .= sprintf('%s.%s', $name, $value);
        }

        return $string;
    }
}
