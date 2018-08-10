<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use CrixuAMG\Decorators\Caches\CacheKey;
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
        // Create the cache key
        $cacheKey = CacheKey::generate($method, ...$args);

        $this->setCacheKey($cacheKey);

        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $args) {
                // Forward the data and cache in the response
                $result = $this->forward($method, ...$args);

                return $this->resourceful($result);
            }
        );
    }
}
