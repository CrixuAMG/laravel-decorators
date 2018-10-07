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
