<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Caches\AbstractCache;

/**
 * Class Handler
 *
 * @package CrixuAMG\Decorators
 */
class Handler
{
    /**
     * @var bool
     */
    private static $cacheEnabled;

    /**
     * @param array $chain
     *
     * @return array|null
     * @throws \Throwable
     */
    public static function makeChain($chain)
    {
        self::$cacheEnabled = config('cache.enabled') ?? false;

        $chain = (array)$chain;

        return $chain
            ? self::handlerFactory($chain)
            : [];
    }

    /**
     * @param array $chain
     *
     * @return null
     */
    public static function handlerFactory(array $chain)
    {
        // Set the cache data if it is not set yet
        if (self::$cacheEnabled === null) {
            self::$cacheEnabled = config('cache.enabled') ?? false;
        }

        $instance = null;

        foreach ($chain as $class) {
            if (!self::$cacheEnabled && get_parent_class($class) === AbstractCache::class) {
                continue;
            }

            $instance = $instance
                ? new $class($instance)
                : new $class;
        }

        return $instance;
    }
}
