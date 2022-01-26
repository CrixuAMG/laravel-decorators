<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Services\AdditionalResourceData;
use CrixuAMG\Decorators\Services\ConfigResolver;
use CrixuAMG\Decorators\Services\QueryResult\CountResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Trait Resultable
 * @package CrixuAMG\Decorators\Traits
 */
trait Resultable
{
    /**
     * @return array
     */
    public static function defaultRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * @param  Builder  $query
     * @param  int  $perPage
     * @param  string|null  $column
     * @param  string  $direction
     * @param  array  $relations
     * @param  bool  $forceCount
     *
     * @return array|LengthAwarePaginator|Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function scopeResult(
        Builder $query,
        int $perPage = 25,
        string $column = 'id',
        string $direction = 'DESC',
        array $relations = [],
        bool $forceCount = false
    ) {
        $this->applyFilters($query);

        if (request()->has('count') || $forceCount) {
            return CountResponse::setCount($query->count());
        }

        $this->addRelationsToQuery($query, $relations);
        $this->applySorting($query, $column, $direction);

        $perPage = (int) $this->getPerPageFromRequest($perPage);
        if ($perPage === 1) {
            return $query->first() ?: abort(404);
        }

        return $perPage > 0
            ? $this->fetchPaginatedResult($query, $perPage)
            : $query->get();
    }

    /**
     * @param  Builder  $query
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\CursorPaginator|LengthAwarePaginator
     */
    private function fetchPaginatedResult(Builder $query, int $perPage)
    {
        return request()->paginate === 'cursor'
            ? $query->cursorPaginate($perPage)
            : $query->paginate($perPage);
    }

    /**
     * Get the column to order and the direction to order the data by
     *
     * @param  string  $orderColumn
     * @param  string  $orderDirection
     * @return array
     */
    protected function getOrderBy(string $orderColumn, string $orderDirection)
    {
        $configOrderColumn = ConfigResolver::get(
            'query_params.order_column',
            'order_column',
            true
        );
        $configOrderDirection = ConfigResolver::get(
            'query_params.order_direction',
            'order_direction',
            true
        );
        $baseOrderColumn = $orderColumn;
        // Make sure that when 'id' (or any other column) is selected/provided, that the column is not ambiguous!
        $orderColumn = request()->input($configOrderColumn) ?? $orderColumn ?? 'id';
        $orderDirection = request()->input($configOrderDirection) ?? $orderDirection ?? 'ASC';

        if (!$this->canBeOrderedByColumn($orderColumn)) {
            $orderColumn = $baseOrderColumn ?: 'id';
        }

        return [
            'column'    => $orderColumn,
            'direction' => strtoupper($orderDirection),
        ];
    }

    /**
     * Add relations to the query
     */
    protected function addRelationsToQuery(Builder &$query, $relations)
    {
        if (empty($relations) && $relations !== false) {
            $relations = get_called_class()::defaultRelations();
        }

        AdditionalResourceData::appendData('relations', $relations);

        if (!empty($relations)) {
            $query = $query->with($relations);
        }
    }

    /**
     * Retrieves the amount of items requested per page
     *
     * @param  int  $maximum
     *
     * @return int
     */
    protected function getPerPageFromRequest(int $maximum = 25): int
    {
        $perPage = request()->input(
                ConfigResolver::get(
                    'query_params.per_page',
                    'per_page',
                    true
                )
            ) ?? config('decorators.pagination');

        return (int) ($perPage > $maximum
            ? $maximum
            : $perPage);
    }

    /**
     * @param  Builder  $query
     *
     * @return Builder
     */
    protected function applyFilters(Builder &$query): Builder
    {
        $filters = $this->getFilters();

        if (!empty($filters)) {
            $modelTable = $this->getTable();

            foreach ($filters as $column => $filter) {
                AdditionalResourceData::appendData('filters', ['column' => $column, 'filter' => $filter]);

                if (stripos($column, $modelTable) === 0) {
                    $column = str_replace(['.', $modelTable], '', $column);
                }

                $filterMethod = $this->getFilterMethod($column);

                if (method_exists($this, $filterMethod)) {
                    // For custom handleUserIdFilter
                    $query = $this->$filterMethod($query, $filter);
                } else {
                    if (!is_array($filter)) {
                        $query->where($column, $filter);
                    } else {
                        $query->whereIn($column, $filter);
                    }
                }
            }
        }

        return $query;
    }

    /**
     * @return array
     */
    protected function getFilters(): array
    {
        $filters = request()->get(
            ConfigResolver::get('query_params.filters', 'filters', true)
        );

        if (empty($filters)) {
            return [];
        }

        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        $filters = (array) $filters;

        $validatedFilters = [];

        if (method_exists($this->definition, 'filterableColumns')) {
            foreach ($this->getDefinitionInstance()->filterableColumns() as $column) {
                if (array_key_exists($column, $filters)) {
                    $validatedFilters[$this->getFilterSelectColumn($column)] = $filters[$column];
                }
            }
        } else {
            foreach ($filters as $column => $filter) {
                $validatedFilters[$this->getFilterSelectColumn($column)] = $filters[$column];
            }
        }

        return $validatedFilters;
    }

    /**
     * @param  string  $column
     * @return bool
     */
    protected function canBeOrderedByColumn(string $column): bool
    {
        if (method_exists($this->definition, 'sortableColumns')) {
            $orderableColumns = $this->getDefinitionInstance()->sortableColumns();
            return empty($orderableColumns) || in_array($column, $orderableColumns);
        }

        return true;
    }

    /**
     * @param  string  $column
     * @return string
     */
    protected function getFilterSelectColumn(string $column): string
    {
        return sprintf('%s.%s', $this->getTable(), $column);
    }

    /**
     * @param  string  $column
     * @return string
     */
    protected function getFilterMethod(string $column): string
    {
        return Str::camel('handle '.\str_replace('.', ' ', $column).'Filter');
    }

    /**
     * @param  Builder  $query
     * @param  string  $column
     * @param  string  $direction
     */
    protected function applySorting(Builder &$query, string $column, string $direction)
    {
        $model = $query->getModel();
        if (method_exists($model, 'handleSorting')) {
            AdditionalResourceData::addData('sorting', ['column' => $column, 'direction' => $direction]);

            $query = $model->handleSorting($query, $column, $direction)
                ->select(
                    sprintf(
                        '%s.*',
                        $model->getTable()
                    )
                );
        } else {
            $order = $this->getOrderBy(
                sprintf(
                    '%s.%s',
                    $model->getTable(),
                    $column
                        ?: $model->getKeyName()
                ),
                $direction
            );
            if ($order['column'] === $model->getKeyName()) {
                $order['column'] = sprintf(
                    '%s.%s',
                    $model->getTable(),
                    $order['column']
                );
            }

            AdditionalResourceData::appendData('sorting', $order);

            $query = $query->orderBy($order['column'], $order['direction']);
        }
    }
}
