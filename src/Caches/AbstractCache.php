<?php

namespace CrixuAMG\Decorators\Caches;

use Exception;
use Throwable;
use Illuminate\Database\Eloquent\Model;
use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\HasForwarding;
use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Contracts\DefinitionContract;
use CrixuAMG\Decorators\Services\AbstractDecoratorContainer;

/**
 * Class AbstractCache
 *
 * @package CrixuAMG\Decorators\Caches
 */
abstract class AbstractCache extends AbstractDecoratorContainer implements DecoratorContract
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
     * @return mixed
     * @throws Throwable
     *
     * @throws Exception
     */
    public function index()
    {
        return $this->forwardCached(__FUNCTION__);
    }

    /**
     * @param Model $model
     * @param mixed ...$relations
     *
     * @return mixed
     * @throws Throwable
     *
     * @throws Exception
     */
    public function show(Model $model, ...$relations)
    {
        return $this->forwardCached(__FUNCTION__, $model, ...$relations);
    }

    /**
     * @param array $data
     *
     * @return mixed
     * @throws Throwable
     *
     * @throws Exception
     */
    public function store(array $data)
    {
        return $this->flushAfterForward(__FUNCTION__, $data);
    }

    /**
     * @param Model $model
     * @param array $data
     *
     * @return mixed
     * @throws Throwable
     *
     * @throws Exception
     */
    public function update(Model $model, array $data)
    {
        // Redirect to our repository
        return $this->flushAfterForward(__FUNCTION__, $model, $data);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     * @throws Throwable
     *
     * @throws Exception
     */
    public function destroy(Model $model)
    {
        return $this->flushAfterForward(__FUNCTION__, $model);
    }

    /**
     * @return DefinitionContract
     * @throws Throwable
     */
    public function definition(): array
    {
        return $this->forwardCached(__FUNCTION__);
    }
}
