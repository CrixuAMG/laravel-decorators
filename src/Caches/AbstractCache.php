<?php

namespace Crixuamg\Decorators\Caches;

use Crixuamg\Decorators\Contracts\DecoratorContract;
use Crixuamg\Decorators\Repositories\AbstractRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

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
     * @param int    $index
     * @param string $cacheKey
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function indexByKey($index = 1, string $cacheKey)
    {
        return $this->forwardCached($cacheKey, null, 'index', $index);
    }

    /**
     * @param string   $cacheKey
     * @param int|null $cacheTime
     * @param array    ...$args
     *
     * @throws Exception
     *
     * @return mixed
     */
    protected function forwardCached(string $cacheKey, int $cacheTime = null, ...$args)
    {
        // Get the calling method
        $method = debug_backtrace()[1]['function'];

        if ($cacheTime === null) {
            $cacheTime = config('decorators.cache_minutes');
        }

        // Verify the method exists on the next iteration
        if (method_exists($this->next, $method)) {
            // Fetch all items from the database
            // in this call, we cache the result.
            return cache()->tags($this->cacheTags)->remember(
                $cacheKey,
                $cacheTime,
                function () use ($method, $args) {
                    // Hand off the task to the repository
                    return $this->next->$method($args);
                }
            );
        }

        throw new UnexpectedValueException('Method ' . $method . ' does not exist or is not callable.');
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    abstract public function show(Model $model);

    /**
     * @param Model  $model
     * @param string $cacheKey
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function showByKey(Model $model, string $cacheKey)
    {
        // in this call, we cache the result of the model.
        return $this->forwardCached($cacheKey, null, 'show', $model);
    }

    /**
     * @param array $data
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function store(array $data)
    {
        // Flush the cache
        flushCache($this->cacheTags);

        // Redirect to our repository
        return $this->next->store($data);
    }

    /**
     * @param Model $model
     * @param array $data
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function update(Model $model, array $data)
    {
        // Flush the cache
        flushCache($this->cacheTags);

        // Redirect to our repository
        return $this->next->update($model, $data);
    }

    /**
     * @param Model $model
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function delete(Model $model)
    {
        // Flush the cache
        flushCache($this->cacheTags);

        // Redirect to our repository
        try {
            $result = $this->next->delete($model);
        } catch (Exception $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param array ...$args
     *
     * @throws \UnexpectedValueException
     *
     * @return mixed
     */
    protected function forward(...$args)
    {
        // Get the calling method
        $method = debug_backtrace()[1]['function'];

        // Verify the method exists on the next iteration
        if (method_exists($this->next, $method)) {
            return $this->next->$method($args);
        }

        throw new UnexpectedValueException('Method ' . $method . ' does not exist or is not callable.');
    }
}