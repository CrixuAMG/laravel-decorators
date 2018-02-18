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
     * @var array
     */
    private $cacheExceptions;

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
                isset($implementedInterfaces[$contract]),
                InterfaceNotImplementedException::class,
                'Contract ' . $contract . ' is not implemented on ' . $class,
                422
            );

            if ($this->checkCache($class)) {
                continue;
            }

            $instance = $instance
                ? new $class($instance)
                : new $class;
        }

        return $instance;
    }

    /**
     * @param array|string $class
     */
    public function enableCacheInEnvironments($environments)
    {
        $this->cacheExceptions = is_array($environments) 
            ? $environments
            : [$environments];
    }

    /**
     * @param $class
     * 
     * @return bool
     */
    public function checkCache($class)
    {
        return (
            !self::$cacheEnabled && 
            isset($this->cacheExceptions) &&
            get_parent_class($class) === AbstractCache::class
        );
    }
}
