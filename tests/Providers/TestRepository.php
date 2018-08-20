<?php

namespace CrixuAMG\Decorators\Test\Providers;

use CrixuAMG\Decorators\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TestRepository
 * @package CrixuAMG\Decorators\Test\Providers
 */
class TestRepository extends AbstractRepository implements TestContract
{
    /**
     * TestRepository constructor.
     */
    public function __construct()
    {
        $this->setModel(new TestModel);
    }

    /**
     * Returns the index
     *
     * @return mixed
     */
    public function index()
    {
        return new Collection();
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
        return new $this->model;
    }

    /**
     * @param bool $paginate
     * @param int  $itemsPerPage
     *
     * @return LengthAwarePaginator|Collection|static[]
     */
    public function simpleIndex(bool $paginate = false, int $itemsPerPage = null)
    {
        return new Collection();
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
        return new $this->model;
    }

    /**
     * @param array  $data
     * @param string $createMethod
     *
     * @return Model
     * @throws \Throwable
     */
    public function simpleStore(array $data, string $createMethod = 'create')
    {
        return new $this->model;
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
    public function delete(Model $model)
    {
        return true;
    }
}
