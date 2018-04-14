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
     * @var array
     */
    private $scopes;
    /**
     * @var array
     */
    private $wheres;
    /**
     * @var int
     */
    private $paginationLimit;

    /**
     * @return mixed
     */
    private function getPaginationLimit()
    {
        return (int)$this->paginationLimit;
    }

    /**
     * @param mixed $paginationLimit
     */
    public function setPaginationLimit(int $paginationLimit)
    {
        $this->paginationLimit = $paginationLimit;

        return $this;
    }

    /**
     * @return mixed
     */
    private function getWheres()
    {
        return (array)$this->wheres;
    }

    /**
     * @param mixed $wheres
     */
    public function setWheres(array $wheres)
    {
        $this->wheres = $wheres;

        return $this;
    }

    /**
     * @return array
     */
    private function getScopes()
    {
        return (array)$this->scopes;
    }

    /**
     * @param mixed $scopes
     */
    public function setScopes(...$scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * Returns the index
     *
     * @return mixed
     */
    abstract public function index();

    /**
     * @param Model $model
     * @param bool  $paginate
     * @param int   $itemsPerPage
     *
     * @return LengthAwarePaginator|Collection|static[]
     */
    public function simpleIndex(Model $model, bool $paginate = false, int $itemsPerPage = null)
    {
        $query = $model->query();

        // If the where is not empty, use it to filter results
        $query = $this->registerWheres($query);

        // If scopes are defined, add them to the query
        $query = $this->registerScopes($query);

        // If the method getDefaultRelations exists, call it to load in relations before returning the model
        if (method_exists(\get_class($model), 'getDefaultRelations')) {
            // Load relationships
            $query->with((array)\get_class($model)::getDefaultRelations());
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
     * @param mixed $relations
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

    /**
     * @param $query
     *
     * @return mixed
     */
    private function registerScopes($query)
    {
        $scopes = $this->getScopes();
        if ($scopes) {
            foreach ($scopes as $scope) {
                $query->{$scope}();
            }
        }

        return $query;
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    private function registerWheres($query)
    {
        $wheres = $this->getWheres();
        if ($wheres) {
            $query->where($wheres);
        }

        return $query;
    }
}