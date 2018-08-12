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
            config(['decorators.cache.enabled' => $enabled]);
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
     * @param bool|null $force
     *
     * @return bool
     */
    public static function forceCacheTags(bool $force = null): bool
    {
        if (!\is_null($force)) {
            config(['decorators.cache.enable_forced_tags' => $force]);
        }

        return (bool)config('decorators.cache.enable_forced_tags');
    }
}
