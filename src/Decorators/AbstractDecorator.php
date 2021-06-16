<?php

namespace CrixuAMG\Decorators\Decorators;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Contracts\DefinitionContract;
use CrixuAMG\Decorators\Services\AbstractDecoratorContainer;
use CrixuAMG\Decorators\Traits\HasForwarding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\UnauthorizedException;

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
     * @param  null  $next
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
     * @param  Model  $model
     *
     * @param  array  $relations
     *
     * @return mixed
     */
    public function show(Model $model, ...$relations)
    {
        return $this->forward(__FUNCTION__, $model, ...$relations);
    }

    /**
     * @param  array  $data
     *
     * @return mixed
     */
    public function store(array $data)
    {
        return $this->forward(__FUNCTION__, $data);
    }

    /**
     * @param  Model  $model
     * @param  array  $data
     *
     * @return mixed
     */
    public function update(Model $model, array $data)
    {
        return $this->forward(__FUNCTION__, $model, $data);
    }

    /**
     * @param  Model  $model
     *
     * @return mixed
     */
    public function destroy(Model $model)
    {
        return $this->forward(__FUNCTION__, $model);
    }

    /**
     * @return DefinitionContract
     * @throws \Throwable
     */
    public function definition(): array
    {
        return $this->forward(__FUNCTION__);
    }

    /**
     * @param  string  $method
     * @param  bool  $statement
     * @param  array  ...$args
     *
     * @return mixed
     * @throws \UnexpectedValueException
     * @throws \Illuminate\Validation\UnauthorizedException
     */
    protected function forwardIfAllowed(string $method, bool $statement, ...$args)
    {
        // Continue if the user is allowed
        if ($statement) {
            return $this->forward($method, ...$args);
        }
        // The user is not allowed to continue
        $this->denyRequest();
    }

    /**
     * @param  string  $exception
     * @param  string  $message
     * @param  int  $code
     */
    protected function denyRequest(
        $exception = UnauthorizedException::class,
        string $message = 'You are not allowed to perform this action.',
        int $code = 403
    ) {
        throw new $exception($message, $code);
    }
}
