<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Exceptions\InterfaceNotImplementedException;
use CrixuAMG\Decorators\Services\AbstractDecoratorContainer;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class Decorator
 *
 * @package CrixuAMG\Decorators
 */
class Decorator
{
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
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->cacheEnabled = Cache::enabled();
    }

    public function decorateIf(string $contract, $chain, string $model = null, $validator = null): void
    {
        if ($validator && is_callable($validator)) {
//            dd($validator);
//            $validator();
        }

        $this->decorate($contract, $chain, $model);
    }

    /**
     * @param  string  $contract
     * @param  array  $chain
     * @param  string|null  $model
     */
    public function decorate(string $contract, $chain, string $model = null): void
    {
        $this->decorateContract(
            $contract,
            (array) $chain,
            $model
        );
    }

    /**
     * Registers a decorated instance of a class
     *
     * @param  string  $contract
     * @param  array  $chain
     * @param  string|null  $model
     */
    private function decorateContract(string $contract, array $chain, string $model = null): void
    {
        $this->app->singleton($contract, function () use ($contract, $chain, $model) {
            return Decorator::processChain($contract, $chain, $model);
        });
    }

    /**
     * @param  string  $contract
     * @param  array  $chain
     * @param  string|null  $model
     * @return mixed
     * @throws \Throwable
     */
    private function processChain(string $contract, array $chain, string $model = null)
    {
        // Create a variable that will hold the instance
        $instance = null;

        foreach ($chain as $class) {
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

            if ($model) {
                /** @var AbstractDecoratorContainer $instance */
                $instance->setModel($model);
            }
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
     * @param  string  $contract
     * @param        $class
     *
     * @throws \Throwable
     */
    private function assertClassImplementsContract(string $contract, $class): void
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
     * @param  string  $class
     * @param  null  $instance
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
    public function enableCacheInEnvironments($environments): void
    {
        $this->cacheExceptions = \is_array($environments)
            ? $environments
            : (array) $environments;
    }
}
