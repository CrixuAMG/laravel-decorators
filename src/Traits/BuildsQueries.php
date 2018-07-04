<?php

namespace CrixuAMG\Decorators\Traits;

use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Trait BuildsQueries
 * @package CrixuAMG\Decorators\Traits
 */
trait BuildsQueries
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
     * @var array
     */
    private $whens;
    /**
     * @var int
     */
    private $paginationLimit;
    /**
     * @var
     */
    private $adaptedQuery;

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return BuildsQueries
     */
    public function __call(string $name, array $arguments)
    {
        // Check if the user is trying to call a dynamic where method (such as setWhereName)
        if (strpos($name, 'setWhere') !== false) {
            // Convert setWhereName to set_where_name
            $name = snake_case($name);
            // Remove set_where_
            $name = str_after('set_where_', $name);
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
    }

    /**
     * @param string $column
     * @param string $string
     *
     * @return BuildsQueries
     */
    public function addWhereLike(string $column, string $string)
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
     * @return BuildsQueries
     */
    public function addWhereBetween(string $column, $firstValue, $secondValue)
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
     * @return BuildsQueries
     * @throws \Throwable
     */
    public function addWhereIn(string $column, ...$arguments)
    {
        $this->validateArgumentCount(\count($arguments), 2, true);

        return $this->addWhere(
            function ($query) use ($column, $arguments) {
                return $query->whereIn($column, $arguments);
            }
        );
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
     * @param array|\Closure $where
     *
     * @return $this
     */
    public function addWhere($where)
    {
        $this->wheres[] = $where;

        return $this;
    }

    /**
     * @param mixed $paginationLimit
     *
     * @return BuildsQueries
     */
    public function setPaginationLimit(int $paginationLimit)
    {
        $this->paginationLimit = $paginationLimit;

        return $this;
    }

    /**
     * @param mixed $wheres
     *
     * @return BuildsQueries
     */
    public function setWheres(array $wheres)
    {
        $this->wheres = $wheres;

        return $this;
    }

    /**
     * @param mixed $scopes
     *
     * @return BuildsQueries
     */
    public function setScopes(...$scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @param Model $model
     *
     * @return BuildsQueries
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return mixed
     */
    private function getWhens()
    {
        return (array)$this->whens;
    }

    /**
     * @return mixed
     */
    private function getPaginationLimit()
    {
        return (int)$this->paginationLimit;
    }

    /**
     * @return mixed
     */
    private function getWheres()
    {
        return (array)$this->wheres;
    }

    /**
     * @return array
     */
    private function getScopes()
    {
        return (array)$this->scopes;
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
     * @param callable $callback
     */
    protected function adaptQuery(callable $callback)
    {
        $this->adaptedQuery = $callback;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return mixed
     */
    private function getAdaptedQuery(\Illuminate\Database\Eloquent\Builder $builder)
    {
        return $this->adaptedQuery
            ? ($this->adaptedQuery)($builder)
            : $builder;
    }

    /**
     * @param int  $argumentsCount
     * @param int  $expectedArgumentCount
     * @param bool $acceptMoreArguments
     *
     * @throws \Throwable
     */
    private function validateArgumentCount(
        int $argumentsCount,
        int $expectedArgumentCount,
        bool $acceptMoreArguments = false
    ): void
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