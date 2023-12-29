<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use Arr;
use Closure;
use Throwable;
use CrixuAMG\Responsable\Responsable;
use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\HasResources;
use CrixuAMG\Decorators\Traits\HasForwarding;
use CrixuAMG\Decorators\Http\Resource\DefinitionResource;
use CrixuAMG\Decorators\Services\AbstractDecoratorContainer;

/**
 * Class AbstractController
 *
 * @package CrixuAMG\Decorators\Http\Controllers
 */
abstract class AbstractController extends AbstractDecoratorContainer
{
    use HasForwarding, HasCaching, HasResources;

    protected string|null $templateRoot;

    /**
     * @param                   $next
     * @param string|array|null $resourceClass
     * @param null $definition
     * @param string ...$cacheTags
     *
     * @return void
     * @throws Throwable
     */
    protected function setup($next, $resourceClass = null, $definition = null, string ...$cacheTags): void
    {
        $definition = $definition ??
            (is_array($next) || $next instanceof Collection)
            ? Arr::get($next, 'definition', null)
            : config(sprintf('decorators.tree.%s.definition', $next), null);

        $this->setNext($next)
            ->setResource($resourceClass)
            ->setDefinition($definition)
            ->setCacheTags(...$cacheTags);
    }

    protected function setTemplateRoot(string $templateRoot): AbstractController
    {
        $this->templateRoot = $templateRoot;
        return $this;
    }

    protected function withoutTemplateRoot(): AbstractController
    {
        $this->templateRoot = null;
        return $this;
    }

    protected function render(mixed $data = null)
    {
        $responsable = Responsable::from($data);

        if (!empty($this->templateRoot)) $responsable->setTemplateRoot($this->templateRoot);

        return $responsable;
    }

    protected function renderWithoutWrapping(mixed $data = null)
    {
        return $this->render($data)
            ->setWithoutWrapping();
    }

    /**
     * @param string $method
     * @param mixed ...$args
     *
     * @return mixed
     */
    protected function forwardResourceful(string $method, ...$args): Responsable
    {
        $result = $this->forward($method, ...$args);

        return $this->render($this->resourceful($result));
    }

    /**
     * @param string $method
     * @param Closure $callback
     * @param mixed ...$args
     *
     * @return mixed
     * @throws Throwable
     */
    protected function forwardCachedCallback(string $method, Closure $callback, ...$args)
    {
        // Forward the data and cache the result.
        return $this->cache(
            function () use ($method, $callback, $args) {
                // Forward the data
                $result = $this->forward($method, ...$args);

                // Return the result after calling the callback function
                return $callback($result);
            },
        );
    }

    /**
     * @param string $method
     * @param Closure $callback
     * @param mixed ...$args
     *
     * @return mixed
     * @throws Throwable
     */
    protected function forwardWithCallback(string $method, Closure $callback, ...$args)
    {
        // Forward the data
        $result = $this->forward($method, ...$args);

        // Return the result after calling the callback function
        return $callback($result);
    }

    /**
     * @return array
     */
    protected function definition()
    {
        return $this->setResource(DefinitionResource::class)
            ->forwardResourceful(__FUNCTION__);
    }
}
