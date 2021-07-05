<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Caches\AbstractCache;
use CrixuAMG\Decorators\Decorator;
use CrixuAMG\Decorators\Decorators\AbstractDecorator;
use CrixuAMG\Decorators\Exceptions\DecoratorsNotSetupException;
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
    protected $next;

    /**
     * @param  mixed  $next
     *
     * @return mixed
     * @throws \Throwable
     */
    public function setNext($next = null)
    {
        // Do this only if next is supplied by the developer.
        if ($next) {
            if (!\is_object($next)) {
                $next = $this->formatNextAndRegister($next);
            }

            if (\is_object($next)) {
                // Validate the next class
                $this->validateNextClass($next);

                // Set the next class so methods can be called on it
                $this->next = $next;
            }
        }

        return $this;
    }

    /**
     * @return AbstractCache|AbstractRepository
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param $next
     *
     * @return mixed
     * @throws \Throwable
     */
    private function formatNextAndRegister($next)
    {
        $contract = null;
        $arguments = null;
        $model = null;
        $validator = null;

        if (\is_string($next)) {
            $next = config(sprintf('decorators.tree.%s', $next));;
        }

        if (\is_array($next)) {
            $contract = $next['contract'] ?? null;
            $arguments = $next['arguments'] ?? null;
            $model = $next['model'] ?? null;
            $validator = $next['validator'] ?? null;
        }

        if ($contract && $arguments) {
            $app = app();

            // If a match has been found, decorate it, then instantiate the newly constructed singleton
            (new Decorator($app))
                ->decorateIf($contract, $arguments, $model, $validator);

            $next = $app->make($contract);
        }

        return $next;
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
            \UnexpectedValueException::class,
            sprintf('Class %s does not implement any allowed parent classes.', \get_class($next)),
            500
        );
    }

    /**
     * @param  string  $method
     * @param  array  ...$args
     *
     * @return mixed
     */
    public function forward(string $method, ...$args)
    {
        throw_unless(
            $this->next,
            DecoratorsNotSetupException::class,
            'Decorators where not correctly setup.',
            500
        );

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
     * @param  string  $method
     *
     * @throws \UnexpectedValueException
     */
    private function throwMethodNotCallable(string $method): void
    {
        throw new UnexpectedValueException(sprintf('Method %s does not exist or is not callable.', $method));
    }
}
