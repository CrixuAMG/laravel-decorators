<?php

namespace CrixuAMG\Decorators\Caches;

use Exception;
use Illuminate\Database\Eloquent\Model;
use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Exceptions\InvalidCacheDataException;
use CrixuAMG\Decorators\Repositories\AbstractRepository;
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
     * @return mixed
     */
    abstract public function index($index = 1);

    /**
     * @param Model $model
     *
     * @return mixed
     */
    abstract public function show(Model $model);

    /**
     * @param array $data
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

        // Flush the cache
        return cache()->tags(...$args)->flush();
    }

    /**
     * @return array
     */
    public function getCacheTags(): array
    {
        return $this->cacheTags;
    }

    /**
     * @param string[] ...$cacheTags
     *
     * @return AbstractCache
     */
    public function setCacheTags(...$cacheTags): AbstractCache
    {
        // Set the firstTag variable that we can use to perform checks on
        $firstTag = reset($cacheTags);

        /** @var array $cacheTags */
        $cacheTags = isset($firstTag) && \is_array($firstTag)
            ? $cacheTags[0]
            : $cacheTags;

        $this->cacheTags = $cacheTags;

        return $this;
    }

    /**
     * @param string $method
     * @param array ...$args
     *
     * @throws \UnexpectedValueException
     *
     * @return mixed
     */
    protected function forward(string $method, ...$args)
    {
        // Verify the method exists on the next iteration and that it is callable
        if (method_exists($this->next, $method) && \is_callable([$this->next, $method])) {
            // Hand off the task to the repository
            return $this->next->$method(...$args);
        }

        // Method does not exist or is not callable
        $this->throwMethodNotCallable($method);
    }

    /**
     * @param string $method
     *
     * @throws \UnexpectedValueException
     */
    private function throwMethodNotCallable(string $method): void
    {
        throw new UnexpectedValueException('Method ' . $method . ' does not exist or is not callable.');
    }

    /**
     * @param Model $model
     * @param array $data
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
     * @param array ...$args
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    protected function forwardCached(string $method, ...$args)
    {
        // Make sure we have the cache tags
        throw_unless(
            $this->cacheTags,
            InvalidCacheDataException::class,
            'The cache tags cannot be empty.',
            422
        );

        // Get the amount of minutes the data should be cached
        $cacheTime = $this->getCacheTime() ?? config('decorators.cache_minutes');

        if (!$cacheTime) {
            // No cache time, don't continue
            // Forward the data and return the response
            return $this->forward($method, ...$args);
        }

        // Create the cache key
        $cacheKey = $this->generateCacheKey($method, $args);

        // Verify the method exists on the next iteration and that it is callable
        if (method_exists($this->next, $method) && \is_callable([$this->next, $method])) {
            // Fetch all items from the database
            // in this call, we cache the result.
            return cache()->tags($this->cacheTags)->remember(
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
     * @return mixed
     */
    public function getCacheTime()
    {
        return $this->cacheTime;
    }

    /**
     * @param int $cacheTime
     *
     * @return AbstractCache
     */
    public function setCacheTime(int $cacheTime): AbstractCache
    {
        $this->cacheTime = $cacheTime;

        return $this;
    }

    /**
     * @param string $method
     * @param        $args
     *
     * @throws \Throwable
     *
     * @return mixed|string
     */
    protected function generateCacheKey(string $method, $args)
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
            implode('.', $this->cacheTags),
            $method,
            serialize($args),
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
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * @param mixed $cacheKey
     *
     * @return AbstractCache
     */
    public function setCacheKey(string $cacheKey): AbstractCache
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @return array
     */
    public function getCacheParameters(): array
    {
        return (array)$this->cacheParameters;
    }

    /**
     * @param array $cacheParameters
     *
     * @return AbstractCache
     */
    public function setCacheParameters(array $cacheParameters): AbstractCache
    {
        $this->cacheParameters = $cacheParameters;

        return $this;
    }
}
