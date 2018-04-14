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

        // Check if modules should get loaded in
        if ((bool)config('salesman.modules_enabled')) {
            $this->registerConfigModules();
        }

        // Set the next class so methods can be called on it
        $this->next = $next;
    }

    /**
     * Register any module registered in the config file
     *
     * @throws \Throwable
     */
    public function registerConfigModules()
    {
        $modulesToRegister = (array)config('salesman.modules');
        if (!empty($modulesToRegister)) {
            $this->registerModules($modulesToRegister);
        }
    }

    /**
     * @param array $modules
     *
     * @throws \Throwable
     */
    public function registerModules(array $modules)
    {
        foreach ($modules as $module => $namespace) {
            $this->registerModule($module, $namespace);
        }
    }

    /**
     * @param        $module
     * @param string $namespace
     *
     * @throws \Throwable
     */
    public function registerModule($module, string $namespace)
    {
        // Validate the namespace before assigning it
        $this->validateNamespace($namespace);

        // The namespace is valid, assign the module
        $this->{$namespace} = class_exists($module)
            ? new $module
            : $module;
    }

    /**
     * @param $page
     *
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function index($page)
    {
        return $this->forward(__FUNCTION__, $page);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function show(Model $model)
    {
        return $this->forward(__FUNCTION__, $model);
    }

    /**
     * @param array $data
     *
     * @return mixed
     * @throws \UnexpectedValueException
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
     * @throws \UnexpectedValueException
     */
    public function update(Model $model, array $data)
    {
        return $this->forward(__FUNCTION__, $model, $data);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function delete(Model $model)
    {
        return $this->forward(__FUNCTION__, $model);
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
     * @param string $message
     *
     * @throws \Illuminate\Validation\UnauthorizedException
     */
    protected function denyRequest(string $message = 'You are not allowed to perform this action.')
    {
        throw new UnauthorizedException($message);
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
