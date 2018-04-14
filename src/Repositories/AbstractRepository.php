<?php

namespace CrixuAMG\Decorators\Repositories;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
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
    /**
     * Returns the index
     *
     * @param $page
     *
     * @return mixed
     */
    abstract public function index($page);

    /**
     * @param Model $model
     * @param array $where
     * @param bool  $paginate
     * @param int   $itemsPerPage
     *
     * @return LengthAwarePaginator|Collection|static[]
     */
    public function simpleIndex(Model $model, array $where = [], bool $paginate = false, int $itemsPerPage = 25)
    {
        $query = $model->query();

        // If the where is not empty, use it to filter results
        if (!empty($where)) {
            $query->where($where);
        }

        // If the method getDefaultRelations exists, call it to load in relations before returning the model
        if (method_exists(\get_class($model), 'getDefaultRelations')) {
            // Load relationships
            $query->with((array)\get_class($model)::getDefaultRelations());
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
     * @param Model  $model
     * @param array  $data
     * @param string $createMethod
     *
     * @return Model
     * @throws \Throwable
     */
    public function simpleStore(Model $model, array $data, string $createMethod = 'create')
    {
        throw_unless(
            is_callable($model, $createMethod),
            UnexpectedValueException::class,
            'The specified method is not callable.',
            422
        );

        return call_user_func_array(
            sprintf(
                '%s::%s',
                $model,
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
     * @param array $relations
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations)
    {
        if (!empty($relations)) {
            $model->load(...$relations);
        } elseif (method_exists(\get_class($model), 'getDefaultRelations')) {
            // If the method getDefaultRelations exists, call it to load in relations before returning the model
            $model->load((array)\get_class($model)::getDefaultRelations());
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