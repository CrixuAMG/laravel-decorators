<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Caches\AbstractCache;
use CrixuAMG\Decorators\Exceptions\InterfaceNotImplementedException;

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
     * @param string $contract
     * @param array  $chain
     *
     * @throws \Throwable
     *
     * @return array|null
     */
    public static function makeChain(string $contract, $chain)
    {
        self::$cacheEnabled = config('cache.enabled') ?? false;

        $chain = (array)$chain;

        return $chain
            ? self::handlerFactory($contract, $chain)
            : [];
    }

    /**
     * @param string $contract
     * @param array  $chain
     *
     * @throws \Throwable
     *
     * @return null
     */
    public static function handlerFactory(string $contract, array $chain)
    {
        // Set the cache data if it is not set yet
        if (self::$cacheEnabled === null) {
            self::$cacheEnabled = config('cache.enabled') ?? false;
        }

        $instance = null;

        foreach ($chain as $class) {
            $implementedInterfaces = class_implements($class);
            throw_unless(
                \count($implementedInterfaces) >= 2 &&
                $implementedInterfaces[1] === $contract,
                InterfaceNotImplementedException::class,
                'Contract ' . $contract . ' is not implemented on ' . $class,
                422
            );

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
