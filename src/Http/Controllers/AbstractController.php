<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\HasForwarding;
use CrixuAMG\Decorators\Traits\HasResources;
use ShareFeed\Http\Controllers\Controller;

/**
 * Class AbstractController
 *
 * @package CrixuAMG\Decorators\Http\Controllers
 */
abstract class AbstractController extends Controller
{
    use HasForwarding, HasCaching, HasResources;

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
     * @param string $method
     * @param mixed  ...$args
     *
     * @return mixed
     * @throws \Throwable
     */
    public function forwardCachedResourceful(string $method, ...$args)
    {
        // Check the cache key, if none is set, generate one
        $this->checkCacheKey($method, ...$args);

        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $args) {
                // Forward the data
                $result = $this->forward($method, ...$args);

                // Return the result resourcefully
                return $this->resourceful($result);
            }
        );
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
        // Check the cache key, if none is set, generate one
        $this->checkCacheKey($method, ...$args);

        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $callback, $args) {
                // Forward the data
                $result = $this->forward($method, ...$args);

                // Return the result resourcefully
                return $callback($result);
            }
        );
    }

    /**
     * @param string $method
     * @param mixed  ...$args
     *
     * @throws \Throwable
     */
    protected function checkCacheKey(string $method, ...$args)
    {
        if (!$this->getCacheKey()) {
            // Create the cache key
            $cacheKey = $this->generateCacheKey($method, ...$args);

            // Set the key
            $this->setCacheKey($cacheKey);
        }
    }
}
