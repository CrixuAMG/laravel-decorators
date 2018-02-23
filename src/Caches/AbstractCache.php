<?php

namespace CrixuAMG\Decorators\Caches;

use CrixuAMG\Decorators\Contracts\RepositoryContract;
use CrixuAMG\Decorators\Repositories\AbstractRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractCache implements RepositoryContract
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
     * @return mixed
     */
    public function indexByKey($index = 1, string $cacheKey)
    {
        // Fetch all items from the database
        // in this call, we cache the result.
        return cache()->tags($this->cacheTags)->remember(
            $cacheKey,
            config('decorators.cache_minutes'),
            function () use ($index) {
                // Hand off the task to the repository
                return $this->next->index($index);
            }
        );
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
     * @return mixed
     */
    public function showByKey(Model $model, string $cacheKey)
    {
        // in this call, we cache the result of the model.
        return cache()->tags($this->cacheTags)->remember(
            $cacheKey,
            config('decorators.cache_minutes'),
            function () use ($model) {
                // Return the exercise
                return $this->next->show($model);
            }
        );
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
}