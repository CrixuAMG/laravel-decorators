<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Exceptions\InvalidCacheDataException;
use Exception;

/**
 * Trait HasCaching
 * @package CrixuAMG\Decorators\Traits
 */
trait HasCaching
{
    /**
     * @var array
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
     * @var array
     */
    protected $cacheParameters;

    /**
     * @param string $method
     * @param array  ...$args
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    protected function forwardCached(string $method, ...$args)
    {
        // Create the cache key
        $cacheKey = $this->generateCacheKey($method, ...$args);

        // Forward the data and cache the result.
        return $this->cache(
            $cacheKey,
            $cacheTime,
            function () use ($method, $args) {
                // Forward the data and cache in the response
                return $this->forward($method, ...$args);
            }
        );
    }

    /**
     * @param          $cacheKey
     * @param callable $callback
     *
     * @return mixed
     */
    protected function cache($cacheKey, callable $callback)
    {
        // Get the cache tags
        $cacheTags = $this->getCacheTags();

        // Make sure we have the cache tags
        throw_unless(
            $cacheTags,
            InvalidCacheDataException::class,
            'The cache tags cannot be empty when using forwardCached.',
            422
        );

        // Get the amount of minutes the data should be cached
        $cacheTime = $this->getCacheTime() ?? config('decorators.cache.minutes');
        if (!$cacheTime || !((bool)config('decorators.cache.enabled'))) {
            // No cache time, don't continue
            // Forward the data and return the response
            return $this->forward($method, ...$args);
        }

        return cache()->tags($cacheTags)->remember(
            $cacheKey,
            $cacheTime,
            ($callback)()
        );
    }

    /**
     * @return array
     */
    protected function getCacheTags(): array
    {
        return (array)$this->cacheTags;
    }

    /**
     * @param string[] ...$cacheTags
     *
     * @return HasCaching
     */
    protected function setCacheTags(...$cacheTags): HasCaching
    {
        // Set the firstTag variable that we can use to perform checks on
        $firstTag = reset($cacheTags);

        /** @var array $cacheTags */
        $cacheTags = $firstTag !== null && \is_array($firstTag) && \count($cacheTags) === 1
            ? $firstTag
            : $cacheTags;

        $this->cacheTags = $cacheTags;

        return $this;
    }

    /**
     * @return mixed
     */
    private function getCacheTime()
    {
        return $this->cacheTime;
    }

    /**
     * @param string $method
     * @param array  ...$args
     *
     * @throws \Throwable
     *
     * @return mixed|string
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
        $configRequestParameters = (array)config('decorators.cache.request_parameters');
        if (!empty($configRequestParameters)) {
            $cacheKeyTemplate .= '%s';
            $cacheKeyParameters[] = json_encode(request()->only($configRequestParameters));
        }

        // Get the custom parameters
        $parameters = $this->getCacheParameters();
        if ($parameters) {
            // There are parameters, build upon the template and parameter set
            foreach ($parameters as $key => $value) {
                if (\is_array($value)) {
                    // If the value is an array, convert it to a JSON string
                    $value = json_encode($value);
                }

                $cacheKeyTemplate .= sprintf('.%s', $this->getCacheKeyType($value));
                $cacheKeyParameters[] = $value;
            }
        }

        // Return the formatted cache key
        return cacheKey($cacheKeyTemplate, $cacheKeyParameters);
    }

    /**
     * @return mixed
     */
    private function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * @return array
     */
    private function getCacheParameters(): array
    {
        return (array)$this->cacheParameters;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function getCacheKeyType($value): string
    {
        // Make sure to preserve float values
        if (\is_float($value)) {
            return '%f';
        }

        // Use it as an unsigned integer
        if (is_numeric($value)) {
            return '%u';
        }

        // Default fall back to string
        return '%s';
    }

    /**
     * @param array $args
     *
     * @throws Exception
     *
     * @return bool|null
     */
    protected function flushCache(...$args)
    {
        if (empty($args)) {
            // No tags have been provided, empty the tags that are attached to the current cache class
            return cache()->tags($this->getCacheTags())->flush();
        }

        if (\count($args) === 1 && reset($args) === true) {
            // Empty the entire cache
            return cache()->flush();
        }

        // Flush the cache using the supplied arguments
        return cache()->tags(...$args)->flush();
    }

    /**
     * @param mixed $cacheKey
     *
     * @return HasCaching
     */
    protected function setCacheKey(string $cacheKey): HasCaching
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @param array $cacheParameters
     *
     * @return HasCaching
     */
    protected function setCacheParameters(array $cacheParameters): HasCaching
    {
        $this->cacheParameters = $cacheParameters;

        return $this;
    }

    /**
     * @param int $cacheTime
     *
     * @return HasCaching
     */
    protected function setCacheTime(int $cacheTime): HasCaching
    {
        $this->cacheTime = $cacheTime;

        return $this;
    }

    /**
     * @param string $method
     * @param mixed  ...$args
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
}