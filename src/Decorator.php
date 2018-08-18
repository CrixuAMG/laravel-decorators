<?php

namespace CrixuAMG\Decorators;

use Carbon\Carbon;
use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Exceptions\InterfaceNotImplementedException;
use CrixuAMG\Decorators\Traits\RouteDecorator;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class Decorator
 *
 * @package CrixuAMG\Decorators
 */
class Decorator
{
    use RouteDecorator;
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
        $this->cacheEnabled = Cache::enabled();
    }

    /**
     * @param string $contract
     * @param array  $chain
     *
     * @throws \Throwable
     */
    public function decorate(string $contract, $chain): void
    {
        $this->decorateContract($contract, (array)$chain);
    }

    /**
     * Registers a decorated instance of a class
     *
     * @param string $contract
     * @param array  $chain
     */
    private function decorateContract(string $contract, array $chain): void
    {
        $this->app->singleton($contract, function () use ($contract, $chain) {
            return Decorator::processChain($contract, $chain);
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
    private function processChain(string $contract, array $chain)
    {
        // Create a variable that will hold the instance
        $instance = null;

        foreach ((array)$chain as $class) {
            // Check if cache is enabled and the class implements the cache class
            if (Cache::implementsCache($class) && !$this->shouldWrapCache()) {
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
    private function shouldWrapCache(): bool
    {
        return $this->cacheEnabled && !isset($this->cacheExceptions[config('app.environment')]);
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
            sprintf(
                'Contract %s is not implemented on %s',
                $contract,
                $class
            ),
            422
        );
    }

    /**
     * @param string $class
     * @param null   $instance
     *
     * @return mixed
     */
    private function getDecoratedInstance($class, $instance = null)
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
            : (array)$environments;
    }
}
