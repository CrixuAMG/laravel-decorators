<?php

namespace CrixuAMG\Decorators\Repositories;

use Exception;
use Throwable;
use Illuminate\Database\Eloquent\Model;
use CrixuAMG\Decorators\Traits\HasDefinitions;
use CrixuAMG\Decorators\Traits\HasTransactions;
use CrixuAMG\Decorators\Services\ConfigResolver;
use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Contracts\DefinitionContract;
use CrixuAMG\Decorators\Services\AbstractDecoratorContainer;
use function get_class;

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
        $method = $this->getIndexMethod();
        return $this->getModel()->$method();
    }

    /**
     * @return string
     */
    protected function getIndexMethod(): string
    {
        $model = $this->getModel();
        return method_exists($model, 'scopeResult')
            ? 'result'
            : ConfigResolver::get('default_index_method', 'paginate', true);
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
        $newModelInstance = $this->getModel()->create($data);

        return $this->show($newModelInstance);
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
        $class = get_class($model);

        if ($this->refreshModelBeforeLoadingRelations) {
            $model = $model->fresh();
        }

        if (empty($relations)) {
            if (method_exists($class, 'showRelations')) {
                // If the method showRelations exists, call it to load in relations before returning the model
                $relations = (array)$class::showRelations();
            } else if (method_exists($class, 'defaultRelations')) {
                // If the method defaultRelations exists, call it to load in relations before returning the model
                $relations = (array)$class::defaultRelations();
            }
        }

        if (isset($this->definition) && method_exists($this->definition, 'requestedRelations')) {
            $relations = array_merge(
                $relations,
                $this->getDefinitionInstance()->requestedRelations()
            );
        }

        $model->load($relations);

        // Return the model
        return $model;
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
     * Delete a model
     *
     * @param Model $model
     *
     * @return mixed
     * @throws Exception
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

    /**
     * @return DefinitionContract
     * @throws Throwable
     */
    public function definition(): array
    {
        if (!empty($this->model) && method_exists($this->model, 'getDefinition')) {
            /** @var HasDefinitions $model */
            $model = $this->getModel();
            return $model->getDefinition();
        }

        return [];
    }
}
