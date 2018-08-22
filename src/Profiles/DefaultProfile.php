<?php

namespace CrixuAMG\Decorators\Profiles;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Contracts\CacheProfileContract;
use Illuminate\Http\Request;

/**
 * Class DefaultProfile
 * @package CrixuAMG\Decorators\CacheProfiles
 */
class DefaultProfile extends AbstractProfile implements CacheProfileContract
{
    /**
     * Get or set the enabled state for caching
     *
     * @param bool|null $enabled
     *
     * @return bool
     */
    public function enabled(bool $enabled = null): bool
    {
        return Cache::enabled($enabled);
    }

    /**
     * Get the cache time
     *
     * @param int|null $time
     *
     * @return int|null
     */
    public function time(int $time = null): ?int
    {
        return Cache::time($time);
    }

    /**
     * Build up the cache key
     *
     * @return null|string
     */
    public function cacheKeyExtension()
    {
        // TODO: Implement cacheKeyExtension() method.
    }

    /**
     * Use data from the array to add to the cache key
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function requestCallback(Request $request)
    {
        // TODO: Implement requestCallback() method.
    }
}
