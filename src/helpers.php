<?php

use CrixuAMG\Decorators\Caches\AbstractCache;

if (!function_exists('cacheEnabled')) {
    /**
     * @return bool
     */
    function cacheEnabled()
    {
        return (bool)config('decorators.cache.enabled');
    }
}

if (!function_exists('cacheKey')) {
    /**
     * @param       $format
     * @param array $parameters
     *
     * @return string
     */
    function cacheKey(string $format, array $parameters)
    {
        return md5(vsprintf($format, $parameters));
    }
}

if (!function_exists('implementsCache')) {
    /**
     * Returns true when the class implements the cache class
     *
     * @param $class
     *
     * @return bool
     */
    function implementsCache($class)
    {
        return get_parent_class($class) === AbstractCache::class;
    }
}
