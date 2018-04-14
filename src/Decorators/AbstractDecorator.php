<?php

namespace CrixuAMG\Decorators\Decorators;

use CrixuAMG\Decorators\Caches\AbstractCache;
use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\UnauthorizedException;
use UnexpectedValueException;

/**
 * Class AbstractDecorator
 *
 * @package CrixuAMG\Decorators\Decorators
 */
abstract class AbstractDecorator implements DecoratorContract
{
    /**
     * @var AbstractCache|AbstractRepository
     */
    protected $next;

    /**
     * AbstractDecorator constructor.
     *
     * @param $next
     *
     * @throws \Throwable
     */
    public function __construct($next)
    {
        // Validate the next class
        $this->validateNextClass($next);

        // Set the next class so methods can be called on it
        $this->next = $next;
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return $this->forward(__FUNCTION__);
    }

    /**
     * @param Model    $model
     * @param bool     $paginate
     * @param int|null $itemsPerPage
     *
     * @return mixed
     */
    public function simpleIndex(Model $model, bool $paginate = false, int $itemsPerPage = null)
    {
        return $this->forward(__FUNCTION__, $model, $paginate, $itemsPerPage);
    }

    /**
     * @param Model  $model
     * @param array  $data
     * @param string $createMethod
     *
     * @return mixed
     */
    public function simpleStore(Model $model, array $data, string $createMethod = 'create')
    {
        // Redirect to our repository
        return $this->forward(__FUNCTION__, $model, $data, $createMethod);
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
     * @param array  ...$args
     *
     * @return mixed
     */
    protected function forward(string $method, ...$args)
    {
        // Verify the method exists on the next iteration and that it is callable
        if (method_exists($this->next, $method) && \is_callable([$this->next, $method])) {
            // Hand off the task to the next decorator
            return $this->next->$method(...$args);
        }

        // Method does not exist or is not callable
        $this->throwMethodNotCallable($method);
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

    /**
     * @param $next
     *
     * @throws \Throwable
     */
    private function validateNextClass($next): void
    {
        $allowedNextClasses = [
            AbstractDecorator::class,
            AbstractCache::class,
            AbstractRepository::class,
        ];

        throw_unless(
            \in_array(get_parent_class($next), $allowedNextClasses, true),
            sprintf('Class %s does not implement any allowed parent classes.', \get_class($next)),
            \UnexpectedValueException::class,
            500
        );
    }

    /**
     * @param string $method
     *
     * @throws \UnexpectedValueException
     */
    private function throwMethodNotCallable(string $method): void
    {
        throw new UnexpectedValueException(sprintf('Method %s does not exist or is not callable.', $method));
    }
}
