<?php

namespace CrixuAMG\Decorators\Repositories;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Traits\BuildsQueries;
use CrixuAMG\Decorators\Traits\Transactionable;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

/**
 * Class AbstractRepository
 *
 * @package CrixuAMG\Decorators\Repositories
 */
abstract class AbstractRepository implements DecoratorContract
{
    use BuildsQueries, Transactionable;
    /**
     * @var Model
     */
    private $model;

    /**
     * Returns the index
     *
     * @return mixed
     */
    abstract public function index();

    /**
     * @param bool $paginate
     * @param int  $itemsPerPage
     *
     * @return LengthAwarePaginator|Collection|static[]
     */
    public function simpleIndex(bool $paginate = false, int $itemsPerPage = null)
    {
        $query = $this->model->query();

        // If the where is not empty, use it to filter results
        $query = $this->registerWheres($query);

        // If whens are defined, add them to the query
        $query = $this->registerWhens($query);

        // If scopes are defined, add them to the query
        $query = $this->registerScopes($query);

        // Get the class
        $class = \get_class($this->model);
        if (method_exists($class, 'getDefaultRelations')) {
            // If the method getDefaultRelations exists, call it to load in relations before returning the data
            $query->with((array)$class::getDefaultRelations());
        }

        if ($paginate && !$itemsPerPage) {
            $itemsPerPage = $this->getPaginationLimit();
            if (!$itemsPerPage) {
                $itemsPerPage = config('decorators.pagination');
            }
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
     * @param array  $data
     * @param string $createMethod
     *
     * @return Model
     * @throws \Throwable
     */
    public function simpleStore(array $data, string $createMethod = 'create')
    {
        throw_unless(
            is_callable($this->model, $createMethod),
            UnexpectedValueException::class,
            'The specified method is not callable.',
            422
        );

        if (\count($data) === 2 && $createMethod === 'updateOrCreate') {
            $firstArray  = reset($data);
            $secondArray = next($data);
            if (is_array($firstArray) && is_array($secondArray)) {
                return call_user_func_array(
                    sprintf(
                        '%s::%s',
                        $this->model,
                        $createMethod
                    ),
                    $firstArray,
                    $secondArray
                );
            }
        }

        return call_user_func_array(
            sprintf(
                '%s::%s',
                $this->model,
                $createMethod
            ),
            $data
        );
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

        if (!empty($relations)) {
            // Load only specified relations
            $model->load(...$relations);
        } elseif (method_exists($class, 'getDefaultRelations')) {
            // If the method getDefaultRelations exists, call it to load in relations before returning the model
            $model->load((array)$class::getDefaultRelations());
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
    public function delete(Model $model)
    {
        try {
            $result = $model->delete();
        } catch (Exception $exception) {
            $result = false;
        }

        return $result;
    }
}
