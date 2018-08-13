<?php

namespace CrixuAMG\Decorators\Http\Middleware;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Traits\HasCaching;

/**
 * Class ResponseCacheMiddleware
 *
 * @package CrixuAMG\Decorators\Http\Middleware
 */
class ResponseCacheMiddleware
{
    use HasCaching;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        // If this was a cachable request and caching is enabled, cache in the response
        if ($request->isMethodCacheable() && Cache::enabled()) {
            // Perform the request, cache in the result
            $response = $this->cache(function () use ($next, $request) {
                return $next($request);
            });
        } else {
            $response = $next($request);
        }

        // Return the response
        return $response;
    }
}
