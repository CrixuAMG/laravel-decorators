<?php

namespace CrixuAMG\Decorators\Decorators;

use Illuminate\Database\Eloquent\Model;
use CrixuAMG\Decorators\Traits\HasForwarding;
use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Services\AbstractDecoratorContainer;

/**
 * Class AbstractDecorator
 *
 * @package CrixuAMG\Decorators\Decorators
 */
abstract class AbstractDecorator extends AbstractDecoratorContainer implements DecoratorContract
{
    use HasForwarding;

    /**
     * AbstractDecorator constructor.
     *
     * @param null $next
     */
    public function __construct($next = null)
    {
        $this->setNext($next);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return $this->forward(__FUNCTION__);
    }

    /**
     * @param Model $model
     *
     * @param array $relations
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations)
    {
        return $this->forward(__FUNCTION__, $model, ...$relations);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        return $this->forward(__FUNCTION__, $data);
    }

    /**
     * @param Model $model
     * @param array $data
     *
     * @return mixed
     */
    public function update(Model $model, array $data)
    {
        return $this->forward(__FUNCTION__, $model, $data);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function destroy(Model $model)
    {
        return $this->forward(__FUNCTION__, $model);
    }

    /**
     * @return array
     */
    public function definition(): array
    {
        return $this->forward(__FUNCTION__);
    }
}
