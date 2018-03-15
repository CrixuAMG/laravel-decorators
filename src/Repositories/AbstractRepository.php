<?php

namespace CrixuAMG\Decorators\Repositories;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
use Exception;
use Illuminate\Database\Eloquent\Model;
use CrixuAMG\Decorators\Contracts\RepositoryContract;

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
     * Create a new model
     *
     * @param array $data
     *
     * @return mixed
     */
    abstract public function store(array $data);

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
     *
     * @return mixed
     */
    public function show(Model $model)
    {
        if (method_exists(\get_class($model), 'getValidRelations')) {
            // Load relationships
            $model->load(\get_class($model)::getValidRelations());
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