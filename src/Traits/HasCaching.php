<?php

namespace CrixuAMG\Decorators\Traits;

use Closure;
use Exception;
use Throwable;
use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Caches\CacheKey;
use CrixuAMG\Decorators\Caches\CacheDriver;
use CrixuAMG\Decorators\Exceptions\InvalidCacheDataException;
use function count;
use function is_array;

/**
 * Trait HasCaching
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait HasCaching
{
    /**
     * @var string[]
     */
    protected $cacheTags;
    /**
     * @var string
     */
    protected $cacheKey;
    /**
     * @var int
     */
    protected $cacheTime;
    /**
     * @var string[]
     */
    protected $cacheParameters;
    /**
     * @var boolean
     */
    protected $flushEntireCache = false;

    /**
     * @param string $method
     * @param array ...$args
     *
     * @return mixed
     * @throws Throwable
     *
     * @throws Exception
     */
    protected function forwardCached(string $method, ...$args)
    {
        // Get the amount of seconds the data should be cached
        $cacheTime = $this->getCacheTime();
        if (!$cacheTime || !Cache::enabled()) {
            // No cache time, don't continue
            return $this->forward($method, ...$args);
        }

        // Generate a new cache key if none is set
        if (!$this->getCacheKey()) {
            $this->setCacheKey($this->generateCacheKey($method, ...$args));
        }

        // Forward the data and cache the result.
        return $this->cache(fn() => $this->forward($method, ...$args));
    }

    /**
     * @return mixed
     */
    private function getCacheTime()
    {
        return $this->cacheTime ?? Cache::time();
    }

    /**
     * @return mixed
     */
    private function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * @param mixed $cacheKey
     *
     * @return mixed
     */
    protected function setCacheKey(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @param string $method
     * @param array ...$args
     *
     * @return mixed|string
     * @throws Throwable
     *
     */
    private function generateCacheKey(string $method, ...$args)
    {
        // Check if there is a cache key set
        $cacheKey = $this->getCacheKey();
        if ($cacheKey) {
            // There is a cache key set, don't go further
            return $cacheKey;
        }

        // Build the basic template and parameter set
        $cacheKeyTemplate = '%s.%s.%s.%s';
        $cacheKeyParameters = [
            config('app.name'),
            implode('.', $this->getCacheTags()),
            $method,
            json_encode($args),
        ];

        // If request parameters are defined, use them to generate a more unique key based on request values
        $configRequestParameters = (array)config('decorators.cache.request_cache_exceptions');
        if (!empty($configRequestParameters)) {
            $cacheKeyTemplate .= '.%s';
            $cacheKeyParameters[] = json_encode(request()->except($configRequestParameters));
        }

        // Get the custom parameters
        $parameters = $this->getCacheParameters();
        if ($parameters) {
            // There are parameters, build upon the template and parameter set
            foreach ($parameters as $key => $value) {
                if (is_array($value)) {
                    // If the value is an array, convert it to a JSON string
                    $value = json_encode($value);
                }

                $cacheKeyTemplate .= sprintf('.%s', CacheKey::getCacheKeyType($value));
                $cacheKeyParameters[] = $value;
            }
        }

        // Return the formatted cache key
        return CacheKey::fromFormat($cacheKeyTemplate, $cacheKeyParameters);
    }

    /**
     * @return array
     */
    protected function getCacheTags(): array
    {
        return array_merge(
            (array)$this->cacheTags,
            (array)config('decorators.cache.default_tags'),
            $this->resolveRequestTags(),
        );
    }

    /**
     * @param string|string[] ...$cacheTags
     *
     * @return mixed
     */
    protected function setCacheTags(...$cacheTags)
    {
        // If the first element is an array, and there is only one element, set it as the tags array
        if (count($cacheTags) === 1 && is_array(reset($cacheTags))) {
            $cacheTags = reset($cacheTags);
        }

        if (
            $this->getCacheTags() !== array_merge(
                $cacheTags,
                (array)config('decorators.cache.default_tags'),
                $this->resolveRequestTags(),
            )
        ) {
            $this->resetCacheKey();
        }

        $this->cacheTags = $cacheTags;

        return $this;
    }

    /**
     * @return array
     */
    protected function resolveRequestTags(): array
    {
        $tags = [];
        $configResolvedTags = (array)config('decorators.cache.request_resolved_parameters');
        if (!empty($configResolvedTags)) {
            foreach ($configResolvedTags as $configResolvedTag) {
                $tag = null;

                $tagParts = explode('.', $configResolvedTag);
                if (reset($tagParts) === 'user') {
                    $user = request()->user();
                    array_shift($tagParts);
                    if ($user) {
                        $user = optional($user);
                        foreach ($tagParts as $tagPart) {
                            $tags[] = $user->{$tagPart};
                        }
                    }
                } else {
                    $tag = data_get(request()->all(), $configResolvedTag);
                }

                if ($tag) {
                    $tags[] = $tag;
                }
            }
        }

        return $tags;
    }

    /**
     * @return array
     */
    private function getCacheParameters(): array
    {
        return (array)$this->cacheParameters;
    }

    /**
     * @param Closure $callback
     *
     * @return mixed
     */
    protected function cache(Closure $callback)
    {
        $cacheTags = null;
        $implementsTags = CacheDriver::implementsTags();

        if ($implementsTags) {
            // Get the cache tags
            $cacheTags = $this->getCacheTags();
        }

        if ($implementsTags) {
            // If tags are implemented in the driver, check if they are required
            $forcedTagsEnabled = Cache::forceCacheTags();

            // If tags are required, but there are none set, throw the exception
            throw_if(
                $forcedTagsEnabled && !$cacheTags,
                InvalidCacheDataException::class,
                'Cache tags are required.',
                500,
            );
        }

        // Get the amount of seconds the data should be cached
        $cacheTime = $this->getCacheTime();
        $cacheKey = $this->getCacheKey() ?? CacheKey::generate(...$cacheTags);

        if (!empty($cacheTags) && $implementsTags) {
            $return = cache()->tags($cacheTags)->remember(
                $cacheKey,
                $cacheTime,
                $callback,
            );
        } else {
            $return = cache()->remember(
                $cacheKey,
                $cacheTime,
                $callback,
            );
        }

        return $return;
    }

    /**
     *
     */
    private function resetCacheKey(): void
    {
        $this->setCacheKey('');
    }

    /**
     * @param array $cacheParameters
     *
     * @return mixed
     */
    protected function setCacheParameters(array $cacheParameters)
    {
        if ($this->getCacheParameters() !== $cacheParameters) {
            $this->resetCacheKey();
        }

        $this->cacheParameters = $cacheParameters;

        return $this;
    }

    /**
     * @param int $cacheTime
     *
     * @return mixed
     */
    protected function setCacheTime(int $cacheTime)
    {
        if ($this->getCacheTime() !== $cacheTime) {
            $this->resetCacheKey();
        }

        $this->cacheTime = $cacheTime;

        return $this;
    }

    /**
     * @param string $method
     * @param mixed ...$args
     *
     * @return mixed
     * @throws Exception
     */
    protected function flushAfterForward(string $method, ...$args)
    {
        // Forward to the repository
        $result = $this->forward($method, ...$args);

        // Flush the cache
        $this->flushCache();

        // Return the result
        return $result;
    }

    /**
     * @param array $tags Only used if the cache driver utilizes tags
     *
     * @return bool|null|void
     * @throws Exception
     */
    protected function flushCache(...$tags)
    {
        // Deprecated
        if (count($tags) === 1 && reset($tags) === true) {
            return cache()->flush();
        }

        // If the cache driver does not support tagging, flush the cache
        if (!CacheDriver::implementsTags() || $this->flushEntireCache) {
            return cache()->flush();
        }

        if (empty($tags)) {
            // No tags have been provided, empty the tags that are attached to the current cache class
            $tags = $this->getCacheTags();
        }

        if (empty($tags)) {
            return cache()->flush();
        }

        // Flush the cache using the supplied arguments
        return cache()->tags(...$tags)->flush();
    }
}
