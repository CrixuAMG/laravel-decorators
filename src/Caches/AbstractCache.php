<?php

namespace CrixuAMG\Decorators\Caches;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\HasForwarding;
use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractCache
 *
 * @package CrixuAMG\Decorators\Caches
 */
abstract class AbstractCache implements DecoratorContract
{
    use HasForwarding, HasCaching;

    /**
     * AbstractCache constructor.
     *
     * @param null $next
     */
    public function __construct($next = null)
    {
        $this->setNext($next);
    }

    /**
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function index()
    {
        return $this->forwardCached(__FUNCTION__);
    }

    /**
     * @param Model $model
     * @param mixed ...$relations
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations)
    {
        return $this->forwardCached(__FUNCTION__, $model, ...$relations);
    }

    /**
     * @param array $data
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function store(array $data)
    {
        return $this->flushAfterForward(__FUNCTION__, $data);
    }

    /**
     * @param Model $model
     * @param array $data
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function update(Model $model, array $data)
    {
        // Redirect to our repository
        return $this->flushAfterForward(__FUNCTION__, $model, $data);
    }

    /**
     * @param Model $model
     *
     * @throws Exception
     * @throws \Throwable
     *
     * @return mixed
     */
    public function destroy(Model $model)
    {
        return $this->flushAfterForward(__FUNCTION__, $model);
    }
}
