<?php

namespace CrixuAMG\Decorators\Test\Providers;

use CrixuAMG\Decorators\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class TestRepository
 * @package CrixuAMG\Decorators\Test\Providers
 */
class TestRepository extends AbstractRepository implements TestContract
{
    /**
     * Returns the index
     *
     * @return mixed
     */
    public function index()
    {
        $this->setModel(TestModel::class);

        return new Collection(
            $this->getModelInstance(),
        );
    }

    /**
     * Create a new model
     *
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        return new TestModel($data);
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
        foreach ($data as $name => $value) {
            $model->{$name} = $value;
        }

        return $model;
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
        return true;
    }

    /**
     * @param int $number
     *
     * @return int
     */
    public function get(int $number): int
    {
        return $number;
    }

    /**
     * @param int $number
     *
     * @return int
     */
    public function getWithoutCacheParameters(int $number): int
    {
        return $number;
    }
}
