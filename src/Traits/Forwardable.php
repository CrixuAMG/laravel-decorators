<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Caches\AbstractCache;
use CrixuAMG\Decorators\Decorators\AbstractDecorator;
use CrixuAMG\Decorators\Repositories\AbstractRepository;
use UnexpectedValueException;

/**
 * Trait Forwardable
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait Forwardable
{
    /**
     * @var AbstractCache|AbstractRepository
     */
    protected $next;

    /**
     * Forwardable constructor.
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
            // Forward the data
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
        throw new UnexpectedValueException(sprintf('Method %s does not exist or is not callable.', $method));
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
}
