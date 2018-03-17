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
     * @param $page
     *
     * @return mixed
     */
    public function index($page);

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function show(Model $model);

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data);

    /**
     * @param Model $model
     * @param array $data
     *
     * @return mixed
     */
    public function update(Model $model, array $data);

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function delete(Model $model);
}