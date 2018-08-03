<?php

namespace CrixuAMG\Decorators\Decorators;

use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Traits\Forwardable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\UnauthorizedException;

/**
 * Class AbstractDecorator
 *
 * @package CrixuAMG\Decorators\Decorators
 */
abstract class AbstractDecorator implements DecoratorContract
{
    use Forwardable;

    /**
     * @return mixed
     */
    public function index()
    {
        return $this->forward(__FUNCTION__);
    }

    /**
     * @param bool     $paginate
     * @param int|null $itemsPerPage
     *
     * @return mixed
     */
    public function simpleIndex(bool $paginate = false, int $itemsPerPage = null)
    {
        return $this->forward(__FUNCTION__, $paginate, $itemsPerPage);
    }

    /**
     * @param array  $data
     * @param string $createMethod
     *
     * @return mixed
     */
    public function simpleStore(array $data, string $createMethod = 'create')
    {
        // Redirect to our repository
        return $this->forward(__FUNCTION__, $data, $createMethod);
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
    public function delete(Model $model)
    {
        return $this->forward(__FUNCTION__, $model);
    }

    /**
     * @param string $method
     * @param bool   $statement
     * @param array  ...$args
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
     * @param string $exception
     * @param string $message
     * @param int    $code
     */
    protected function denyRequest(
        $exception = UnauthorizedException::class,
        string $message = 'You are not allowed to perform this action.',
        int $code = 403
    )
    {
        throw new $exception($message, $code);
    }

    /**
     * @param string $namespace
     *
     * @throws \Throwable
     */
    protected function validateNamespace(string $namespace): void
    {
        throw_if(
            method_exists($this, $namespace),
            sprintf(
                'Namespace %s exists as a method and cannot be used as an alias.',
                $namespace
            ),
            \UnexpectedValueException::class,
            422
        );

        throw_if(
            isset($this->{$namespace}),
            sprintf(
                'Namespace %s already exists within the AbstractDecorator class and cannot be used as an alias.',
                $namespace
            ),
            \UnexpectedValueException::class,
            422
        );
    }
}
