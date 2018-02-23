<?php

namespace CrixuAMG\Decorators;

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
	 * @var string
	 */
	private $contract;
	/**
	 * @var array
	 */
	private $cacheExceptions;

	/**
	 * @param string $contract
	 * @param array  $chain
	 *
	 * @throws \Throwable
	 */
	public function decorate(string $contract, $chain)
	{
		$this->cacheEnabled = config('decorators.cache_enabled') ?? false;
		$this->contract = $contract;

		$this->registerDecoratedInstance((array)$chain);
	}

	/**
	 * @param array $chain
	 *
	 * @throws \Throwable
	 *
	 * @return mixed
	 */
	public function handlerFactory($chain)
	{
		// Set the cache data if it is not set yet
		if ($this->cacheEnabled === null) {
			$this->cacheEnabled = config('decorators.cache_enabled') ?? false;
		}

		return $this->processChain($chain);
	}

	/**
	 * @param $chain
	 *
	 * @throws \Throwable
	 *
	 * @return mixed
	 */
	private function processChain($chain)
	{
		// Create a variable that will hold the instance
		$instance = null;

		foreach ((array)$chain as $parentClass => $class) {
			// Check if cache is enabled and the class implements the cache class
			if ($this->checkCache($class)) {
				continue;
			}

			/**
			 * Make sure the class implements the provided contract
			 * Throws an exception if the class does not implement the contract
			 */
			$this->assertClassImplementsContract($class);

			// Decorate the instance with the class
			$instance = $this->getDecoratedInstance($class, $instance);
		}

		// Return the instance
		return $instance;
	}

	/**
	 * @return bool
	 */
	public function checkCache($class)
	{
		return (
			!$this->cacheEnabled &&
			!isset($this->cacheExceptions) &&
			implementsCache($class)
		);
	}

	/**
	 * @param $class
	 *
	 * @throws \Throwable
	 */
	private function assertClassImplementsContract($class)
	{
		$implementedInterfaces = class_implements($class);

		throw_unless(
			isset($implementedInterfaces[$this->contract]),
			InterfaceNotImplementedException::class,
			'Contract ' . $this->contract . ' is not implemented on ' . $class,
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
	 * Registers a decorated instance of a class
	 *
	 * @param $instance
	 */
	private function registerDecoratedInstance($instance)
	{
		$this->app->singleton($this->contract, function () use ($instance) {
			return Handler::handlerFactory($instance);
		});
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
