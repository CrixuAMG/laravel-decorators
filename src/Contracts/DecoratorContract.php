<?php

namespace CrixuAMG\Decorators\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface DecoratorContract
 *
 * @package CrixuAMG\Decorators\Contracts
 */
interface DecoratorContract
{
    /**
     * @return mixed
     */
    public function index();

    /**
     * @param  Model  $model
     * @param  mixed  ...$relations
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations);

    /**
     * @param  array  $data
     *
     * @return mixed
     */
    public function store(array $data);

    /**
     * @param  Model  $model
     * @param  array  $data
     *
     * @return mixed
     */
    public function update(Model $model, array $data);

    /**
     * @param  Model  $model
     *
     * @return mixed
     */
    public function destroy(Model $model);

    /**
     * @return array
     */
    public function definition(): array;
}
