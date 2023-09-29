<?php

namespace CrixuAMG\Decorators\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use CrixuAMG\Decorators\Services\ConfigResolver;
use Illuminate\Contracts\Pagination\CursorPaginator;
use CrixuAMG\Decorators\Services\AdditionalResourceData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use CrixuAMG\Decorators\Services\QueryResult\CountResponse;
use function str_replace;

/**
 * Trait Resultable
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait Resultable
{
    /**
     * @param Builder $query
     * @param int $perPage
     * @param string|null $column
     * @param string $direction
     * @param array $relations
     * @param bool $forceCount
     *
     * @return array|LengthAwarePaginator|Builder|Model|object
     */
    public function scopeResult(
        Builder $query,
        int     $perPage = 25,
        string  $column = 'id',
        string  $direction = 'DESC',
        array   $relations = [],
        bool    $forceCount = false,
    ) {
        $this->applyFilters($query);
        $this->applyScopes($query);

        if (request()->has('count') || $forceCount) {
            return CountResponse::setCount($query->count());
        }

        $this->addRelationsToQuery($query, $relations);
        $this->applySorting($query, $column, $direction);

        $perPage = (int)$this->getPerPageFromRequest($perPage);
        if ($perPage === 1) {
            return $query->first() ?: abort(404);
        }

        return $perPage > 0
            ? $this->fetchPaginatedResult($query, $perPage)
            : $query->get();
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     * @deprecated filters will be deleted in 2024, move to using scopes instead
     */
    protected function applyFilters(Builder &$query): Builder
    {
        $filters = $this->getFilters();

        if (!empty($filters)) {
            $modelTable = $this->getTable();

            foreach ($filters as $column => $filter) {
                AdditionalResourceData::appendData('filters', [
                    'column' => $column,
                    'filter' => $filter,
                ]);

                if (stripos($column, $modelTable) === 0) {
                    $column = str_replace([
                        '.',
                        $modelTable,
                    ], '', $column);
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
            ConfigResolver::get('query_params.filters', 'filters', true),
        );

        if (empty($filters)) {
            return [];
        }

        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        $filters = (array)$filters;

        $validatedFilters = [];

        if (!empty($this->definition) && method_exists($this->definition, 'filterableColumns')) {
            foreach ($this->getDefinitionInstance()->filterableColumns() as $column) {
                if (array_key_exists($column, $filters)) {
                    $validatedFilters[$this->getFilterSelectColumn($column)] = $filters[$column];
                }
            }
        } else {
            foreach ($filters as $column => $filter) {
                $validatedFilters[$this->getFilterSelectColumn($column)] = $filter;
            }
        }

        return $validatedFilters;
    }

    /**
     * @param string $column
     *
     * @return string
     */
    protected function getFilterSelectColumn(string $column): string
    {
        return sprintf('%s.%s', $this->getTable(), $column);
    }

    /**
     * @param string $column
     *
     * @return string
     */
    protected function getFilterMethod(string $column): string
    {
        return Str::camel('handle ' . str_replace('.', ' ', $column) . 'Filter');
    }

    public function applyScopes(Builder &$query)
    {
        $scopes = $this->scopes();

        foreach ($scopes as $identifier => $instance) {
            AdditionalResourceData::appendData('scopes', [
                'scope' => $identifier,
            ]);

            $query->withGlobalScope($identifier, $instance);
        }
    }

    /**
     * @return array
     */
    protected function scopes(): array
    {
        $scopes = request()->get(
            ConfigResolver::get('query_params.scopes', 'scope', true),
        );

        if (empty($scopes)) {
            return [];
        }

        $scopes = explode('|', (string)$scopes);
        $validatedScopes = [];
        $parseScope = fn($scope) => explode(':', $scope);

        if (!empty($this->definition) && method_exists($this->definition, 'scopes')) {
            $allowedScopes = $this->getDefinitionInstance()->scopes();
            foreach ($scopes as $scope) {
                [$scopeName] = $parseScope($scope);

                if (array_key_exists($scopeName, $allowedScopes)) {
                    $validatedScopes[$scopeName] = $allowedScopes[$scopeName]();
                }
            }
        }

        return $validatedScopes;
    }

    /**
     * Add relations to the query
     */
    protected function addRelationsToQuery(Builder &$query, $relations)
    {
        if (empty($relations) && $relations !== false) {
            $relations = get_called_class()::defaultRelations();
        }

        if (!empty($this->definition) && method_exists($this->definition, 'requestedRelations')) {
            $relations = array_merge(
                $relations,
                $this->getDefinitionInstance()->requestedRelations(),
            );
        }

        if (!empty($relations)) {
            AdditionalResourceData::appendData('relations', $relations);

            $query = $query->with($relations);
        }
    }

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
     * @param Builder $query
     * @param string $column
     * @param string $direction
     */
    protected function applySorting(Builder &$query, string $column, string $direction)
    {
        $model = $query->getModel();
        if (method_exists($model, 'handleSorting')) {
            AdditionalResourceData::addData('sorting', [
                'column'    => $column,
                'direction' => $direction,
            ]);

            $query = $model->handleSorting($query, $column, $direction)
                ->select(
                    sprintf(
                        '%s.*',
                        $model->getTable(),
                    ),
                );
        } else {
            $order = $this->getOrderBy(
                sprintf(
                    '%s.%s',
                    $model->getTable(),
                    $column
                        ?: $model->getKeyName(),
                ),
                $direction,
            );
            if ($order['column'] === $model->getKeyName()) {
                $order['column'] = sprintf(
                    '%s.%s',
                    $model->getTable(),
                    $order['column'],
                );
            }

            AdditionalResourceData::appendData('sorting', $order);

            $query = $query->orderBy($order['column'], $order['direction']);
        }
    }

    /**
     * Get the column to order and the direction to order the data by
     *
     * @param string $orderColumn
     * @param string $orderDirection
     *
     * @return array
     */
    protected function getOrderBy(string $orderColumn, string $orderDirection)
    {
        $configOrderColumn = ConfigResolver::get(
            'query_params.order_column',
            'order_column',
            true,
        );
        $configOrderDirection = ConfigResolver::get(
            'query_params.order_direction',
            'order_direction',
            true,
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
     * @param string $column
     *
     * @return bool
     */
    protected function canBeOrderedByColumn(string $column): bool
    {
        if (!empty($this->definition) && method_exists($this->definition, 'sortableColumns')) {
            $orderableColumns = $this->getDefinitionInstance()->sortableColumns();
            return empty($orderableColumns) || in_array($column, $orderableColumns);
        }

        return true;
    }

    /**
     * Retrieves the amount of items requested per page
     *
     * @param int $maximum
     *
     * @return int
     */
    protected function getPerPageFromRequest(int $maximum = 25): int
    {
        $perPage = request()->input(
            ConfigResolver::get(
                'query_params.per_page',
                'per_page',
                true,
            ),
        ) ?? config('decorators.pagination');

        return $perPage ?? $maximum;
    }

    /**
     * @param Builder $query
     * @param int $perPage
     *
     * @return CursorPaginator|LengthAwarePaginator
     */
    protected function fetchPaginatedResult(Builder $query, int $perPage)
    {
        return request()->paginate === 'cursor'
            ? $query->cursorPaginate($perPage)
            : $query->paginate($perPage);
    }
}
