<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use CrixuAMG\Decorators\Traits\HasCacheProfiles;
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
    use HasForwarding, HasCaching, HasCacheProfiles, HasResources;

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
        if (!$this->getCacheKey()) {
            // Create the cache key
            $cacheKey = $this->generateCacheKey($method, ...$args);

            // Set the key
            $this->setCacheKey($cacheKey);
        }

        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $args) {
                // Forward the data and cache in the response
                $result = $this->forward($method, ...$args);

                // Return the result resourcefully
                return $this->resourceful($result);
            }
        );
    }
}
