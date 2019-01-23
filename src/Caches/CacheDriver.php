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
     * @var \Illuminate\Config\Repository|mixed|string
     */
    private static $driver;

    /**
     * CacheDriver constructor.
     *
     * @param string|null $driver
     */
    public function __construct(string $driver = null)
    {
        self::$driver = $driver ?? config('cache.default', 'file');
    }

    /**
     * @param string|null $driver
     *
     * @return bool
     */
    public static function implementsTags(string $driver = null): bool
    {
        if (!$driver) {
            $driver = self::$driver;
        }

        return method_exists(Cache::store($driver)->getStore(), 'tags');
    }
}
