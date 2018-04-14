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
     * @var
     */
    private $whens;

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return bool|AbstractRepository
     */
    public function __call(string $name, array $arguments)
    {
        // Check if the user is trying to call a dynamic where method (such as setWhereName)
        if (strpos($name, 'setWhere') !== false) {
            // Convert setWhereName to set_where_name
            $name = snake_case($name);
            // Remove set_where_
            $name = str_replace('set_where_', '', $name);
            // If the name is still valid, continue
            if ($name) {
                return $this->addWhere(
                    function ($query) use ($name, $arguments) {
                        return $query->where($name, reset($arguments));
                    }
                );
            }
        }

        // No match could be found or something went wrong
        return false;
    }

    /**
     * @param string $column
     * @param string $string
     *
     * @return AbstractRepository
     */
    public function setWhereLike(string $column, string $string)
    {
        return $this->addWhere(
            function ($query) use ($column, $string) {
                return $query->where($column, 'LIKE', $string);
            }
        );
    }

    /**
     * @param string $column
     * @param        $firstValue
     * @param        $secondValue
     *
     * @return AbstractRepository
     */
    public function setWhereBetween(string $column, $firstValue, $secondValue)
    {
        return $this->addWhere(
            function ($query) use ($column, $firstValue, $secondValue) {
                return $query->whereBetween($column, [
                    $firstValue,
                    $secondValue,
                ]);
            }
        );
    }

    /**
     * @param string $column
     * @param mixed  ...$arguments
     *
     * @return AbstractRepository
     * @throws \Throwable
     */
    public function setWhereIn(string $column, ...$arguments)
    {
        $this->validateArgumentCount(\count($arguments), 2, true);

        return $this->addWhere(
            function ($query) use ($column, $arguments) {
                return $query->whereIn($column, $arguments);
            }
        );
    }

    /**
     * @return mixed
     */
    private function getWhens()
    {
        return (array)$this->whens;
    }

    /**
     * @param          $statement
     * @param \Closure $callback
     *
     * @return $this
     */
    public function addWhen($statement, \Closure $callback)
    {
        if ((bool)$statement) {
            $this->whens[] = $callback;
        }

        return $this;
    }

    /**
     * @param          $statement
     * @param \Closure $callback
     *
     * @return $this
     */
    public function addWhere($where)
    {
        $this->wheres[] = $where;

        return $this;
    }

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
     *
     * @return AbstractRepository
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
     *
     * @return AbstractRepository
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
     *
     * @return AbstractRepository
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

        // If whens are defined, add them to the query
        $query = $this->registerWhens($query);

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

    /**
     * @param $query
     *
     * @return mixed
     */
    private function registerWhens($query)
    {
        $whens = $this->getWhens();
        if ($whens) {
            foreach ($whens as $callback) {
                $query->when(true, $callback);
            }
        }

        return $query;
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
            foreach ($wheres as $column => $value) {
                if ($value instanceof \Closure) {
                    $query->where($value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    /**
     * @param int  $argumentsCount
     * @param int  $expectedArgumentCount
     * @param bool $acceptMoreArguments
     *
     * @throws \Throwable
     */
    private function validateArgumentCount(int $argumentsCount, int $expectedArgumentCount, bool $acceptMoreArguments = false): void
    {
        $statement = $acceptMoreArguments
            ? $argumentsCount >= 2
            : $argumentsCount === 2;

        throw_unless(
            $statement,
            \InvalidArgumentException::class,
            sprintf('%d arguments were supplied and exactly %s were expected', $argumentsCount, $expectedArgumentCount),
            422
        );
    }
}