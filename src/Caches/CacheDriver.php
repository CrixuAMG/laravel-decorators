<?php

namespace CrixuAMG\Decorators\Caches;

use Illuminate\Support\Facades\Cache;

/**
 * Class CacheDriver
 *
 * @package CrixuAMG\Decorators\Caches
 */
class CacheDriver
{
    /**
     * @param string|null $driver
     *
     * @return bool
     */
    public static function implementsTags(string $driver = null): bool
    {
        return method_exists(Cache::store($driver)->getStore(), 'tags');
    }
}
