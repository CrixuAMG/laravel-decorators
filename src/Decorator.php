<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Exceptions\InterfaceNotImplementedException;
use CrixuAMG\Decorators\Traits\RouteDataProvider;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class Decorator
 *
 * @package CrixuAMG\Decorators
 */
class Decorator
{
    use RouteDataProvider;
    /**
     * @var bool
     */
    private $cacheEnabled;
    /**
     * @var array
     */
    private $cacheExceptions;
    /**
     * @var Application
     */
    private $app;

    /**
     * Decorator constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $contract
     * @param array  $chain
     *
     * @throws \Throwable
     */
    public function decorate(string $contract, $chain): void
    {
        $this->cacheEnabled = config('decorators.cache_enabled') ?? false;

        $this->registerDecoratedInstance($contract, (array)$chain);
    }

    /**
     * Registers a decorated instance of a class
     *
     * @param string $contract
     * @param        $instance
     */
    private function registerDecoratedInstance(string $contract, $instance): void
    {
        $this->app->singleton($contract, function () use ($contract, $instance) {
            return Decorator::handlerFactory($contract, $instance);
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
            $this->cacheEnabled = (bool)config('decorators.cache_enabled') ?? false;
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
        // Create a variable that will hold the instance
        $instance = null;

        foreach ((array)$chain as $parentClass => $class) {
            // Check if cache is enabled and the class implements the cache class
            if (implementsCache($class) && !$this->shouldWrapCache()) {
                continue;
            }

            /**
             * Make sure the class implements the provided contract
             * Throws an exception if the class does not implement the contract
             */
            $this->assertClassImplementsContract($contract, $class);

            // Decorate the instance with the class
            $instance = $this->getDecoratedInstance($class, $instance);
        }

        // Return the instance
        return $instance;
    }

    /**
     * @return bool
     */
    private function shouldWrapCache()
    {
        return (
            !$this->cacheEnabled &&
            !isset($this->cacheExceptions)
        );
    }

    /**
     * @param string $contract
     * @param        $class
     *
     * @throws \Throwable
     */
    private function assertClassImplementsContract(string $contract, $class)
    {
        $implementedInterfaces = class_implements($class);

        throw_unless(
            isset($implementedInterfaces[$contract]),
            InterfaceNotImplementedException::class,
            'Contract ' . $contract . ' is not implemented on ' . $class,
            422
        );
    }

    /**
     * @param string $class
     * @param        $instance
     *
     * @return mixed
     */
    private function getDecoratedInstance($class, $instance)
    {
        return $instance
            ? new $class($instance)
            : new $class;
    }

    /**
     * @param $environments
     */
    public function enableCacheInEnvironments($environments)
    {
        $this->cacheExceptions = is_array($environments)
            ? $environments
            : [$environments];
    }
}
