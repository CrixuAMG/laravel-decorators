<?php

namespace CrixuAMG\Decorators\Repositories;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Services\AbstractDecoratorContainer;
use CrixuAMG\Decorators\Traits\HasTransactions;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractRepository
 *
 * @package CrixuAMG\Decorators\Repositories
 */
abstract class AbstractRepository extends AbstractDecoratorContainer implements DecoratorContract
{
    use HasTransactions;

    /**
     * @var bool
     */
    protected $refreshModelBeforeLoadingRelations;

    /**
     * Returns the index
     *
     * @return mixed
     */
    public function index()
    {
        // TODO: set a index method in the config, default 'paginate'
        $method = method_exists($this->getModelInstance(), 'scopeResult')
            ? 'result'
            : 'paginate';

        return $this->getModelInstance()->$method();
    }

    /**
     * Create a new model
     *
     * @param  array  $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        $newModelInstance = $this->getModelInstance()->create($data);

        return $this->show($newModelInstance);
    }

    /**
     * Update a model
     *
     * @param  Model  $model
     * @param  array  $data
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
     * @param  Model  $model
     * @param  mixed  $relations
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations)
    {
        // Get the class
        $class = \get_class($model);

        if ($this->refreshModelBeforeLoadingRelations) {
            $model = $model->fresh();
        }

        if (!empty($relations)) {
            // Load only specified relations
            $model->load(...$relations);
        } elseif (method_exists($class, 'showRelations')) {
            // If the method showRelations exists, call it to load in relations before returning the model
            $model->load((array) $class::showRelations());
        } elseif (method_exists($class, 'defaultRelations')) {
            // If the method defaultRelations exists, call it to load in relations before returning the model
            $model->load((array) $class::defaultRelations());
        }

        // Return the model
        return $model;
    }

    /**
     * Delete a model
     *
     * @param  Model  $model
     *
     * @return mixed
     * @throws \Exception
     */
    public function destroy(Model $model)
    {
        try {
            $result = $model->delete() ?? false;
        } catch (Exception $exception) {
            $result = false;
        } finally {
            return $result;
        }
    }
}
