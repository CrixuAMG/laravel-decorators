<?php

namespace CrixuAMG\Decorators\Repositories;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Traits\HasTransactions;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractRepository
 *
 * @package CrixuAMG\Decorators\Repositories
 */
abstract class AbstractRepository implements DecoratorContract
{
    use HasTransactions;
    /**
     * @var Model
     */
    protected $model;
    /**
     * @var bool
     */
    protected $refreshModelBeforeLoadingRelations;

    /**
     * @param Model $model
     *
     * @return AbstractRepository
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Returns the index
     *
     * @return mixed
     */
    abstract public function index();

    /**
     * @param bool $paginate
     * @param int $itemsPerPage
     *
     * @return LengthAwarePaginator|Collection|static[]
     */
    public function simpleIndex(bool $paginate = false, int $itemsPerPage = null)
    {
        $query = $this->model->query();

        // Get the class
        $class = \get_class($this->model);
        if (method_exists($class, 'defaultRelations')) {
            // If the method defaultRelations exists, call it to load in relations before returning the data
            $query->with((array)$class::defaultRelations());
        }

        if ($paginate && !$itemsPerPage) {
            $itemsPerPage = (int)config('decorators.pagination');
        }

        // Return the data
        return $paginate && $itemsPerPage
            ? $query->paginate($itemsPerPage)
            : $query->get();
    }

    /**
     * Create a new model
     *
     * @param array $data
     *
     * @return mixed
     */
    abstract public function store(array $data);

    /**
     * @param array $data
     * @param string $createMethod
     *
     * @return Model
     * @throws \Throwable
     */
    public function simpleStore(array $data, string $createMethod = 'create')
    {
        $classAndMethod = sprintf(
            '%s::%s',
            \get_class($this->model),
            $createMethod
        );

        if (($createMethod === 'updateOrCreate' || $createMethod === 'firstOrCreate') && \count($data) === 2) {
            $firstArray = reset($data);
            $secondArray = next($data);
            if (\is_array($firstArray) && \is_array($secondArray)) {
                return $classAndMethod($firstArray, $secondArray);
            }
        }

        return $classAndMethod($data);
    }

    /**
     * Update a model
     *
     * @param Model $model
     * @param array $data
     *
     * @return mixed
     */
    public function update(Model $model, array $data)
    {
        // Update the model
        $model->update($data);

        // Return the model with loaded relations
        return $this->show($model);
    }

    /**
     * Return a single model
     *
     * @param Model $model
     * @param mixed $relations
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations)
    {
        // Get the class
        $class = \get_class($model);

        if ($this->refreshModelBeforeLoadingRelations) {
            $model = $model->fresh();
        }

        if (!empty($relations)) {
            // Load only specified relations
            $model->load(...$relations);
        } else if (method_exists($class, 'showRelations')) {
            // If the method showRelations exists, call it to load in relations before returning the model
            $model->load((array)$class::showRelations());
        } else if (method_exists($class, 'defaultRelations')) {
            // If the method defaultRelations exists, call it to load in relations before returning the model
            $model->load((array)$class::defaultRelations());
        }

        // Return the model
        return $model;
    }

    /**
     * Delete a model
     *
     * @param Model $model
     *
     * @return mixed
     * @throws \Exception
     */
    public function destroy(Model $model)
    {
        try {
            $result = $model->delete() ?? false;
        } catch (Exception $exception) {
            $result = false;
        } finally {
            return $result;
        }
    }
}
