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
     * @param             $next
     * @param string|null $resourceClass
     * @param string      ...$cacheTags
     */
    public function setup($next, string $resourceClass = null, string ...$cacheTags)
    {
        // Set next
        $this->setNext($next);

        if ($resourceClass) {
            // Set the resource if it was supplied
            $this->setResource($resourceClass);
        }

        if (!empty($cacheTags)) {
            // Set the cache tags
            $this->setCacheTags(...$cacheTags);
        }
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
}
