<?php

namespace CrixuAMG\Decorators\Services;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractDecoratorContainer
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @param  string  $model
     * @return string
     */
    public function setModel(string $model): string
    {
        return $this->model = $model;
    }

    /**
     * @return Model
     */
    protected function getModelInstance()
    {
        return new $this->model;
    }
}
