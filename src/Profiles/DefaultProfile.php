<?php

namespace CrixuAMG\Decorators\Profiles;

use CrixuAMG\Decorators\Contracts\CacheProfileContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DefaultProfile
 * @package CrixuAMG\Decorators\Profiles
 */
class DefaultProfile extends AbstractProfile implements CacheProfileContract
{
    /**
     * @return mixed
     */
    public function index()
    {
        // TODO: Implement index() method.
    }

    /**
     * @param bool     $paginate
     * @param int|null $itemsPerPage
     *
     * @return mixed
     */
    public function simpleIndex(bool $paginate = false, int $itemsPerPage = null)
    {
        // TODO: Implement simpleIndex() method.
    }

    /**
     * @param Model $model
     * @param mixed ...$relations
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations)
    {
        // TODO: Implement show() method.
    }

    /**
     * @param mixed ...$relations
     *
     * @return mixed
     */
    public function simpleShow(...$relations)
    {
        // TODO: Implement simpleShow() method.
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        // TODO: Implement store() method.
    }

    /**
     * @param array  $data
     * @param string $createMethod
     *
     * @return mixed
     */
    public function simpleStore(array $data, string $createMethod = 'create')
    {
        // TODO: Implement simpleStore() method.
    }

    /**
     * @param Model $model
     * @param array $data
     *
     * @return mixed
     */
    public function update(Model $model, array $data)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function delete(Model $model)
    {
        // TODO: Implement delete() method.
    }
}
