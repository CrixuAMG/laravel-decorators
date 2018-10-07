<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Caches\AbstractCache;
use CrixuAMG\Decorators\Decorators\AbstractDecorator;
use CrixuAMG\Decorators\Repositories\AbstractRepository;
use UnexpectedValueException;

/**
 * Trait HasForwarding
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait HasForwarding
{
    /**
     * @var AbstractCache|AbstractRepository
     */
    public $next;

    /**
     * @param null $next
     *
     * @return HasForwarding
     */
    public function setNext($next = null)
    {
        // Do this only if next is supplied by the developer.
        if ($next) {
            // Validate the next class
            $this->validateNextClass($next);

            // Set the next class so methods can be called on it
            $this->next = $next;
        }

        return $this;
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
        if (method_exists($this->next, $method) && \is_callable([
                $this->next,
                $method,
            ])) {
            // Forward the data
            return $this->next->$method(...$args);
        }

        // Method does not exist or is not callable
        $this->throwMethodNotCallable($method);
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
