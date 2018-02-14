<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Caches\AbstractCache;
use CrixuAMG\Decorators\Exceptions\InterfaceNotImplementedException;
use Illuminate\Support\ServiceProvider;

/**
 * Class Handler
 *
 * @package CrixuAMG\Decorators
 */
class Handler extends ServiceProvider
{
    /**
     * @var bool
     */
    private $cacheEnabled;

    /**
     * @param string $contract
     * @param array  $chain
     *
     * @throws \Throwable
     */
    public function decorate(string $contract, $chain)
    {
        $this->cacheEnabled = config('cache.enabled') ?? false;

        $decoratedChain = $chain
            ? $this->handlerFactory($contract, (array)$chain)
            : [];

        $this->app->singleton($contract, function () use ($contract, $decoratedChain) {
            return Handler::handlerFactory(
                $contract,
                $decoratedChain
            );
        });
    }

    /**
     * @param string $contract
     * @param array  $chain
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    public function handlerFactory(string $contract, $chain)
    {
        // Set the cache data if it is not set yet
        if ($this->cacheEnabled === null) {
            $this->cacheEnabled = config('cache.enabled') ?? false;
        }

        return $this->processChain($contract, $chain);
    }

    /**
     * @param string $contract
     * @param        $chain
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    private function processChain(string $contract, $chain)
    {
        $instance = null;

        foreach ((array)$chain as $parentClass => $class) {
            if (!$this->cacheEnabled && get_parent_class($class) === AbstractCache::class) {
                continue;
            }

            $instance = $this->getDecoratedInstance($contract, $class, $instance);
        }

        return $instance;
    }

    /**
     * @param string $contract
     * @param string $class
     * @param        $instance
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    private function getDecoratedInstance(string $contract, $class, $instance)
    {
        $implementedInterfaces = class_implements($class);

        throw_unless(
            isset($implementedInterfaces[$contract]),
            InterfaceNotImplementedException::class,
            'Contract ' . $contract . ' is not implemented on ' . $class,
            422
        );

        return $instance
            ? new $class($instance)
            : new $class;
    }
}
