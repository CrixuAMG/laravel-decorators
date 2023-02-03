<?php

namespace CrixuAMG\Decorators\Services;

use Illuminate\Database\Eloquent\Model;
use CrixuAMG\Decorators\Traits\HasDefinitions;

abstract class AbstractDecoratorContainer
{
    use HasDefinitions;

    /**
     * @var string
     */
    protected $model;

    /**
     * @return Model
     */
    protected function getModel()
    {
        $model = new $this->model();

        if (is_callable($this->model, 'setDefinition')) {
            $model = $model->setDefinition($this->definition);
        }

        return $model;
    }

    /**
     * @param string $model
     *
     * @return string
     */
    public function setModel(string $model)
    {
        $this->model = $model;

        return $this;
    }
}
