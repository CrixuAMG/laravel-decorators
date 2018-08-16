<?php

namespace CrixuAMG\Decorators\Contracts;

use Illuminate\Http\Request;

/**
 * Interface CacheProfileContract
 * @package CrixuAMG\Decorators\Contracts
 */
interface CacheProfileContract
{
    /**
     * Get or set the enabled state for caching
     *
     * @param bool|null $enabled
     *
     * @return bool
     */
    public function enabled(bool $enabled = null): bool;

    /**
     * Get the cache time
     *
     * @param int|null $time
     *
     * @return int|null
     */
    public function time(int $time = null): ?int;

    /**
     * Build up the cache key
     *
     * @return null|string
     */
    public function cacheKeyExtension(): ?string;

    /**
     * Use data from the array to add to the cache key
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function requestCallback(Request $request): ?array;
}
