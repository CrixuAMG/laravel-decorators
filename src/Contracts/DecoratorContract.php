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
     * @param bool     $paginate
     * @param int|null $itemsPerPage
     *
     * @return mixed
     */
    public function simpleIndex(bool $paginate = false, int $itemsPerPage = null);

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
     * @param array  $data
     * @param string $createMethod
     *
     * @return mixed
     */
    public function simpleStore(array $data, string $createMethod = 'create');

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
