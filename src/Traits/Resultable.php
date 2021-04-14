<?php

namespace CrixuAMG\Decorators\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

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
     * @param  array  $relations
     * @return Builder
     */
    public function scopeWithRelations(Builder $query, array $relations = [])
    {
        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query;
    }

    /**
     * @param  Builder  $query
     * @param  int  $perPage
     * @param  string|null  $column
     * @param  string  $direction
     * @param  array  $relations
     * @param  bool  $forceCount
     *
     * @return array|LengthAwarePaginator
     */
    public function scopeResult(
        Builder $query,
        int $perPage = 25,
        string $column = 'id',
        string $direction = 'DESC',
        array $relations = [],
        bool $forceCount = false
    ) {
        $result = null;

        $model = $query->getModel();
        if (method_exists($model, 'handleFilter')) {
            $query = $model->handleFilter($query);
        }

        if (request()->has('count') || $forceCount) {
            $result = [
                'count' => $query->count(),
            ];
        } else {
            if (method_exists($model, 'handleSorting')) {
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
                $query = $query->orderBy($order['column'], $order['direction']);
            }

            $this->addRelationsToQuery($query, $relations);

            $perPage = (int) $this->getPerPageFromRequest($perPage);
            if ($perPage === 1) {
                $result = $query->first() ?: abort(404);
            } elseif ($perPage > 0) {
                $result = $query->paginate($perPage);
            } else {
                $result = $query->get();
            }
        }

        return $result;
    }

    /**
     * @param  Builder  $query
     *
     * @return Builder
     */
    public function handleFilter(Builder $query): Builder
    {
        $filters = $this->getFilters();
        $model = $query->getModel();

        if (!empty($filters)) {
            foreach ($filters as $column => $filter) {
                $camelCase = \Illuminate\Support\Str::camel('handle '.\str_replace('.', ' ', $column).'Filter');

                if (method_exists($model, $camelCase)) {
                    // For custom handleUserIdFilter
                    $query = $model->$camelCase($query, $filter);
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
    public function getFilters(): array
    {
        $filters = request()->get('filters');

        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        $filters = (array) $filters;

        $validatedFilters = [];
        foreach ($this->filterableData() as $column) {
            if (!empty($filters[$column])) {
                $validatedFilters[$this->getFilterSelectColumn($column)] = $filters[$column];
            }
        }

        return $validatedFilters;
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
     * @return array
     */
    public function filterableData(): array
    {
        return [];
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
        $baseOrderColumn = $orderColumn;
        // Make sure that when 'id' (or any other column) is selected/provided, that the column is not ambiguous!
        $orderColumn = strtolower(request()->input('order_column') ?? $orderColumn ?? 'id');
        $orderDirection = request()->input('order_direction') ?? $orderDirection ?? 'ASC';

        if (!$this->canBeOrderedByColumn($orderColumn)) {
            $orderColumn = $baseOrderColumn ?: 'id';
        }

        return [
            'column'    => $orderColumn,
            'direction' => strtoupper($orderDirection),
        ];
    }

    /**
     * Retrieves the amount of items requested per page
     *
     * @param  int  $maximum
     *
     * @return int
     */
    protected function getPerPageFromRequest(int $maximum = 25)
    {
        $perPage = request()->input('per_page') ?? config('decorators.pagination');

        return (int) ($perPage > $maximum
            ? $maximum
            : $perPage);
    }

    /**
     * Get default relations from class
     */
    protected function getRelationsFromModel()
    {
        return get_called_class()::defaultRelations();
    }

    /**
     * Add relations to the query
     */
    protected function addRelationsToQuery(Builder &$query, $relations)
    {
        if (empty($relations) && $relations !== false) {
            $relations = $this->getRelationsFromModel();
        }

        if (!empty($relations)) {
            $query = $query->with($relations);
        }
    }

    protected function canBeOrderedByColumn(string $column)
    {
        $orderableColumns = get_called_class()::orderableColumns();
        return empty($orderableColumns) || in_array($column, $orderableColumns);
    }

    public static function orderableColumns(): array
    {
        return [];
    }
}
