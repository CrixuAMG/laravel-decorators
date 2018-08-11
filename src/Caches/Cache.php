<?php

namespace CrixuAMG\Decorators\Caches;

/**
 * Class Cache
 * @package CrixuAMG\Decorators\Caches
 */
class Cache
{
    /**
     * @return bool
     */
    public static function enabled(): bool
    {
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
}
