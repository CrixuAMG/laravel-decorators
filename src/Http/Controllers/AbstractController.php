<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\HasForwarding;
use CrixuAMG\Decorators\Traits\HasResources;

/**
 * Class AbstractController
 *
 * @package CrixuAMG\Decorators\Http\Controllers
 */
abstract class AbstractController
{
    use HasForwarding, HasCaching, HasResources;

    /**
     * @param                   $next
     * @param string|array|null $resourceClass
     * @param string            ...$cacheTags
     *
     * @return void
     * @throws \Throwable
     */
    public function setup($next, $resourceClass = null, string ...$cacheTags): void
    {
        $this->setNext($next)
            ->setResource($resourceClass)
            ->setCacheTags(...$cacheTags);
    }

    /**
     * @param string $method
     * @param mixed  ...$args
     *
     * @return mixed
     * @throws \Throwable
     */
    public function forwardCachedResourceful(string $method, ...$args)
    {
        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $args) {
                // Forward the data and return the result resourcefully
                return $this->forwardResourceful($method, ...$args);
            }
        );
    }

    /**
     * @param string $method
     * @param mixed  ...$args
     *
     * @return mixed
     */
    public function forwardResourceful(string $method, ...$args)
    {
        // Forward the data
        $result = $this->forward($method, ...$args);

        // Return the result resourcefully
        return $this->resourceful($result);
    }

    /**
     * @param string   $method
     * @param \Closure $callback
     * @param mixed    ...$args
     *
     * @return mixed
     * @throws \Throwable
     */
    public function forwardCachedCallback(string $method, \Closure $callback, ...$args)
    {
        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $callback, $args) {
                // Forward the data
                $result = $this->forward($method, ...$args);

                // Return the result after calling the callback function
                return $callback($result);
            }
        );
    }

    /**
     * @param string $method
     * @param \Closure $callback
     * @param mixed ...$args
     *
     * @return mixed
     * @throws \Throwable
     */
    public function forwardWithCallback(string $method, \Closure $callback, ...$args)
    {
        // Forward the data
        $result = $this->forward($method, ...$args);

        // Return the result after calling the callback function
        return $callback($result);
    }
}
