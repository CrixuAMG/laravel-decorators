<?php

namespace CrixuAMG\Decorators\Caches;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Exceptions\InvalidCacheDataException;
use CrixuAMG\Decorators\Repositories\AbstractRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

/**
 * Class AbstractCache
 *
 * @package CrixuAMG\Decorators\Caches
 */
abstract class AbstractCache implements DecoratorContract
{

    /**
     * @var AbstractRepository
     */
    protected $next;
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
     * AbstractCache constructor.
     *
     * @param AbstractRepository $next
     */
    public function __construct(AbstractRepository $next)
    {
        $this->next = $next;
    }

    /**
     * @param int $index
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function index($index = 1)
    {
        return $this->forwardCached(__FUNCTION__, $index);
    }

    /**
     * @param Model $model
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function show(Model $model)
    {
        return $this->forwardCached(__FUNCTION__, $model);
    }

    /**
     * @param array $data
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function store(array $data)
    {
        // Flush the cache
        $this->flushCache();

        // Redirect to our repository
        return $this->forward(__FUNCTION__, $data);
    }

    /**
     * @param Model $model
     * @param array $data
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function update(Model $model, array $data)
    {
        // Flush the cache
        $this->flushCache();

        // Redirect to our repository
        return $this->forward(__FUNCTION__, $model, $data);
    }

    /**
     * @param Model $model
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function delete(Model $model)
    {
        // Flush the cache
        $this->flushCache();

        // Redirect to our repository
        return $this->forward(__FUNCTION__, $model);
    }

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
        // Get the cache tags
        $cacheTags = $this->getCacheTags();

        // Make sure we have the cache tags
        throw_unless(
            $cacheTags,
            InvalidCacheDataException::class,
            'The cache tags cannot be empty.',
            422
        );

        // Get the amount of minutes the data should be cached
        $cacheTime = $this->getCacheTime() ?? config('salesman.cache_minutes');

        if (!$cacheTime) {
            // No cache time, don't continue
            // Forward the data and return the response
            return $this->forward($method, ...$args);
        }

        // Create the cache key
        $cacheKey = $this->generateCacheKey($method, ...$args);

        // Verify the method exists on the next iteration and that it is callable
        if (method_exists($this->next, $method) && \is_callable([$this->next, $method])) {
            // Fetch all items from the database
            // in this call, we cache the result.
            return cache()->tags($cacheTags)->remember(
                $cacheKey,
                $cacheTime,
                function () use ($method, $args) {
                    // Forward the data and cache in the response
                    return $this->forward($method, ...$args);
                }
            );
        }

        // Method does not exist or is not callable
        $this->throwMethodNotCallable($method);
    }

    /**
     * @param string $method
     * @param array  ...$args
     *
     * @throws \UnexpectedValueException
     *
     * @return mixed
     */
    protected function forward(string $method, ...$args)
    {
        // Verify the method exists on the next iteration and that it is callable
        if (method_exists($this->next, $method) && \is_callable([$this->next, $method])) {
            // Forward the data
            return $this->next->$method(...$args);
        }

        // Method does not exist or is not callable
        $this->throwMethodNotCallable($method);
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
     * @return array
     */
    protected function getCacheTags(): array
    {
        return $this->cacheTags;
    }

    /**
     * @param string[] ...$cacheTags
     *
     * @return AbstractCache
     */
    protected function setCacheTags(...$cacheTags): AbstractCache
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
     * @param mixed $cacheKey
     *
     * @return AbstractCache
     */
    protected function setCacheKey(string $cacheKey): AbstractCache
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @param array $cacheParameters
     *
     * @return AbstractCache
     */
    protected function setCacheParameters(array $cacheParameters): AbstractCache
    {
        $this->cacheParameters = $cacheParameters;

        return $this;
    }

    /**
     * @param int $cacheTime
     *
     * @return AbstractCache
     */
    protected function setCacheTime(int $cacheTime): AbstractCache
    {
        $this->cacheTime = $cacheTime;

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
     *
     * @throws \UnexpectedValueException
     */
    private function throwMethodNotCallable(string $method): void
    {
        throw new UnexpectedValueException(sprintf('Method %s does not exist or is not callable.', $method));
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
        $cacheKeyTemplate = '%s.%s.%s';
        $cacheKeyParameters = [
            implode('.', $this->getCacheTags()),
            $method,
            json_encode($args),
        ];

        // Get the custom parameters
        $parameters = $this->getCacheParameters();
        if ($parameters) {
            // There are parameters, build upon the template and parameter set
            foreach ($parameters as $key => $value) {
                $cacheKeyTemplate .= '.' . (is_numeric($value) ? '%u' : '%s');
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
}
