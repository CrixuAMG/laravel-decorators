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
     * @param Model    $model
     * @param bool     $paginate
     * @param int|null $itemsPerPage
     *
     * @return mixed
     */
    public function simpleIndex(Model $model, bool $paginate = false, int $itemsPerPage = null);

    /**
     * @param Model $model
     * @param mixed ...$relations
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations);

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data);

    /**
     * @param Model  $model
     * @param array  $data
     * @param string $createMethod
     *
     * @return mixed
     */
    public function simpleStore(Model $model, array $data, string $createMethod = 'create');

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