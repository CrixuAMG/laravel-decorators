<?php

namespace CrixuAMG\Decorators\Caches;

/**
 * Class Cache
 * @package CrixuAMG\Decorators\Caches
 */
class Cache
{
    /**
     * @param bool|null $enabled
     *
     * @return bool
     */
    public static function enabled(bool $enabled = null): bool
    {
        if (!\is_null($enabled)) {
            $enabled ? self::enable()
                : self::disable();
        }

        return (bool)config('decorators.cache.enabled');
    }

    /**
     * @return int
     */
    public static function time(): int
    {
        return (int)config('decorators.cache.minutes');
    }

    /**
     * @param $class
     *
     * @return bool
     */
    public static function implementsCache($class): bool
    {
        return get_parent_class($class) === AbstractCache::class;
    }

    /**
     *
     */
    public static function enable()
    {
        config(['decorators.cache.enabled' => true]);
    }

    /**
     *
     */
    public static function disable()
    {
        config(['decorators.cache.enabled' => false]);
    }
}
