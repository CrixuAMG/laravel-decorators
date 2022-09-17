<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use Closure;
use CrixuAMG\Decorators\Http\Resource\DefinitionResource;
use CrixuAMG\Decorators\Services\AbstractDecoratorContainer;
use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\HasForwarding;
use CrixuAMG\Decorators\Traits\HasResources;
use CrixuAMG\Decorators\Traits\SmartReturns;
use Throwable;

/**
 * Class AbstractController
 *
 * @package CrixuAMG\Decorators\Http\Controllers
 */
abstract class AbstractController extends AbstractDecoratorContainer
{
    use HasForwarding, HasCaching, HasResources, SmartReturns;

    /**
     * @param                   $next
     * @param  string|array|null  $resourceClass
     * @param  null  $definition
     * @param  string  ...$cacheTags
     *
     * @return void
     * @throws Throwable
     */
    public function setup($next, $resourceClass = null, $definition = null, string ...$cacheTags): void
    {
        $this->setNext($next)
            ->setResource($resourceClass)
            ->setDefinition($definition ?? config(sprintf('%s.definition', is_array($next) ? Arr::get($next, 'decorator', null) : $next), null))
            ->setCacheTags(...$cacheTags);
    }

    /**
     * @param  string  $method
     * @param  mixed  ...$args
     *
     * @return mixed
     * @throws Throwable
     */
    public function forwardCachedResourceful(string $method, ...$args)
    {
        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $args) {
                // Forward the data and return the result resourcefully
                return $this->forwardResourceful($method, ...$args);
            }
        );
    }

    /**
     * @param  string  $method
     * @param  mixed  ...$args
     *
     * @return mixed
     */
    public function forwardResourceful(string $method, ...$args)
    {
        // Forward the data
        $result = $this->forward($method, ...$args);

        // Return the result resourcefully
        return $this->resourceful($result);
    }

    /**
     * @param  string  $method
     * @param  Closure  $callback
     * @param  mixed  ...$args
     *
     * @return mixed
     * @throws Throwable
     */
    public function forwardCachedCallback(string $method, Closure $callback, ...$args)
    {
        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $callback, $args) {
                // Forward the data
                $result = $this->forward($method, ...$args);

                // Return the result after calling the callback function
                return $callback($result);
            }
        );
    }

    /**
     * @param  string  $method
     * @param  Closure  $callback
     * @param  mixed  ...$args
     *
     * @return mixed
     * @throws Throwable
     */
    public function forwardWithCallback(string $method, Closure $callback, ...$args)
    {
        // Forward the data
        $result = $this->forward($method, ...$args);

        // Return the result after calling the callback function
        return $callback($result);
    }

    /**
     * @return array
     */
    public function definition()
    {
        return $this->setResource(DefinitionResource::class)
            ->forwardResourceful(__FUNCTION__);
    }
}
