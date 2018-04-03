<?php

namespace CrixuAMG\Decorators\Decorators;

use CrixuAMG\Decorators\Caches\AbstractCache;
use CrixuAMG\Decorators\Contracts\DecoratorContract;
use CrixuAMG\Decorators\Modules\EventModule;
use CrixuAMG\Decorators\Modules\SecurityModule;
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
    use SecurityModule, EventModule;
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
            'Class ' . \get_class($next) . ' does not implement any allowed parent classes.',
            \UnexpectedValueException::class,
            500
        );
    }

    /**
     * @param $page
     *
     * @return mixed
     */
    public function index($page)
    {
        return $this->forward(__FUNCTION__, $page);
    }

    /**
     * @param string $method
     * @param array  ...$args
     *
     * @throws \UnexpectedValueException
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
     *
     * @throws \UnexpectedValueException
     */
    private function throwMethodNotCallable(string $method): void
    {
        throw new UnexpectedValueException('Method ' . $method . ' does not exist or is not callable.');
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function show(Model $model)
    {
        return $this->forward(__FUNCTION__, $model);
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
     * @param string $message
     */
    protected function denyRequest(string $message = 'You are not allowed to perform this action.')
    {
        throw new UnauthorizedException($message);
    }
}