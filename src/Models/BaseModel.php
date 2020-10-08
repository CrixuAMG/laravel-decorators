<?php

namespace CrixuMG\Decorators\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 *
 * @package CrixuMG\Decorators\Models
 */
class BaseModel extends Model
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
     * @param Builder $query
     * @param array $relations
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
     * @param Builder $query
     * @param int $perPage
     * @param string|null $column
     * @param string $direction
     * @param array $relations
     * @param bool $forceCount
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
    )
    {
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
                $query = $query->orderBy($order['column'], $order['direction']);
            }

            if (!empty($relations)) {
                $query = $query->with($relations);
            }

            $perPage = $this->getPerPageFromRequest($perPage);
            if ($perPage > 0) {
                $result = $query->paginate($perPage);
            } else {
                $result = $query->get();
            }
        }

        return $result;
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function handleFilter(Builder $query): Builder
    {
        $filters = $this->getFilters();

        if (!empty($filters)) {
            foreach ($filters as $column => $filter) {
                if (!is_array($filter)) {
                    $query->where($column, $filter);
                } else {
                    $query->whereIn($column, $filter);
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

        $filters = (array)$filters;

        foreach ($this->filterableData() as $column) {
            if (!empty($filters[$column])) {
                $filters[sprintf('%s.%s', $this->getTable(), $column)] = $filters[$column];
            }
        }

        return collect($filters)->filter(function ($filter, $key) {
            return in_array($key, $this->filterableData());
        })->toArray();
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
     * @param string $orderColumn
     * @param string $orderDirection
     * @return array
     */
    private function getOrderBy(string $orderColumn, string $orderDirection)
    {
        // Make sure that when 'id' (or any other column) is selected/provided, that the column is not ambiguous!
        $orderColumn    = request()->input('order_column') ?? $orderColumn ?? 'id';
        $orderDirection = request()->input('order_direction') ?? $orderDirection ?? 'ASC';

        return [
            'column'    => strtolower($orderColumn),
            'direction' => strtoupper($orderDirection),
        ];
    }

    /**
     * Retrieves the amount of items requested per page
     *
     * @param int $maximum
     *
     * @return int
     */
    private function getPerPageFromRequest(int $maximum = 25)
    {
        $perPage = request()->input('per_page') ?? config('decorators.pagination');

        return (int)($perPage > $maximum
            ? $maximum
            : $perPage);
    }
}
