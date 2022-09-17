<?php

namespace CrixuAMG\Decorators\Services;

use CrixuAMG\Decorators\Traits\HasDefinitions;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractDecoratorContainer
{
    use HasDefinitions;

    /**
     * @var string
     */
    protected $model;

    /**
     * @param  string  $model
     * @return string
     */
    public function setModel(string $model)
    {
        $this->model = $model;
        
        return $this;
    }

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
}
