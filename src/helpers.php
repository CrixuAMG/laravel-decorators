<?php

use CrixuAMG\Decorators\Caches\AbstractCache;

if (!function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @param  mixed             $condition
     * @param  \Throwable|string $exception
     * @param  array             ...$parameters
     *
     * @return mixed
     * @throws \Throwable
     */
    function throw_if($condition, $exception, ...$parameters)
    {
        if ($condition) {
            throw is_string($exception) ? new $exception(...$parameters) : $exception;
        }

        return $condition;
    }
}

if (!function_exists('throw_unless')) {
    /**
     * Throw the given exception unless the given condition is true.
     *
     * @param  mixed             $condition
     * @param  \Throwable|string $exception
     * @param  array             ...$parameters
     *
     * @return mixed
     * @throws \Throwable
     */
    function throw_unless($condition, $exception, ...$parameters)
    {
        if (!$condition) {
            throw is_string($exception) ? new $exception(...$parameters) : $exception;
        }

        return $condition;
    }
}

if (!function_exists('flushCache')) {
    /**
     * @param array $tags
     *
     * @return mixed
     *
     * @throws Exception
     */
    function flushCache(array $tags = [])
    {
        return !$tags
            ? cache()->flush()
            : cache()->tags($tags)->flush();
    }
}

if (!function_exists('cacheEnabled')) {
    /**
     * @return bool
     */
    function cacheEnabled()
    {
        return config('dealmaker.cache_enabled');
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
    function implementsCache($class) {
        return get_parent_class($class) === AbstractCache::class;
    }
}
