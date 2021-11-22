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

    /**
     * @param  string  $contract
     * @param $chain
     * @param  string|null  $model
     * @param  string|null  $definition
     * @param  null  $validator
     */
    public function decorateIf(string $contract, $chain, string $model = null, string $definition = null, $validator = null): void
    {
        if ($validator && class_exists($validator)) {
            $validator = new $validator();

            abort_unless(
                $validator->validate(),
                $validator->getResponseCode()
            );
        }

        $this->decorate($contract, $chain, $model, $definition);
    }

    /**
     * @param  string  $contract
     * @param  array  $chain
     * @param  string|null  $model
     * @param  string|null  $definition
     */
    public function decorate(string $contract, $chain, string $model = null, string $definition = null): void
    {
        $this->decorateContract(
            $contract,
            (array) $chain,
            $model,
            $definition
        );
    }

    /**
     * Registers a decorated instance of a class
     *
     * @param  string  $contract
     * @param  array  $chain
     * @param  string|null  $model
     * @param  string|null  $definition
     */
    private function decorateContract(string $contract, array $chain, string $model = null, string $definition = null): void
    {
        $this->app->singleton($contract, function () use ($contract, $chain, $model, $definition) {
            return Decorator::processChain($contract, $chain, $model, $definition);
        });
    }

    /**
     * @param  string  $contract
     * @param  array  $chain
     * @param  string|null  $model
     * @param  string|null  $definition
     * @return mixed
     * @throws \Throwable
     */
    private function processChain(string $contract, array $chain, string $model = null, string $definition = null)
    {
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
            /** @var AbstractDecoratorContainer $instance */
            $instance = $this->getDecoratedInstance($class, $instance);

            if ($model) {
                $instance->setModel($model);
            }

            if ($definition) {
                $instance->setDefinition($definition);
            }
        }

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
            : new $class();
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
