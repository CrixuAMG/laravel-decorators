<?php

namespace CrixuAMG\Decorators;

/**
 * Class Handler
 *
 * @package CrixuAMG\Decorators
 */
class Handler
{
    /**
     * @var array|null
     */
    private $handler;
    private $cacheEnabled;

    /**
     * Handler constructor.
     *
     * @param $chain
     */
    public function __construct($chain = [])
    {
        $this->cacheEnabled = config('cache.enabled') ?? false;

        if ($chain) {
            $this->handler = static::makeChain($chain);
        }
    }

    /**
     * @param $chain
     *
     * @return array|null
     */
    public static function makeChain($chain)
    {
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
        $instance = null;

        foreach ($chain as $class) {
            // Todo::check cache

            $instance = $instance
                ? new $class($instance)
                : new $class;
        }

        return $instance;
    }
}
