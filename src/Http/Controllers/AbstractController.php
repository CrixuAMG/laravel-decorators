<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class AbstractController
 *
 * @package CrixuAMG\Decorators\Http\Controllers
 */
abstract class AbstractController
{
    /**
     * @var
     */
    private $repository;

    /**
     * @var
     */
    private $resource;
    /**
     * @var
     */
    private $data;
    /**
     * @var int
     */
    private $succesfullRequestCode = 200;
    /**
     * @var int
     */
    private $unsuccesfullRequestCode = 500;

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param mixed $repository
     *
     * @return AbstractController
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     *
     * @return AbstractController
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return AbstractController
     */
    public function __call($name, $arguments)
    {
        $name = ltrim($name, '_');
        if (method_exists($this->repository, $name) && \is_callable([$this->repository, $name])) {
            $this->data = $this->repository->{$name}(...$arguments);
        }

        return $this;
    }

    /**
     * @param null $resourceClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resourceful($resourceClass = null)
    {
        if (!$resourceClass) {
            $calledClass = get_called_class();
            $namespace = str_before($calledClass, 'Http');
            $calledClass = str_after($calledClass, 'Controllers\\');
            $calledClass = str_before($calledClass, 'Controller');

            $classToCheck = sprintf('%sHttp\Resources\%sResource', $namespace, $calledClass);

            if (class_exists($classToCheck)) {
                $resourceClass = $classToCheck;
            }
        }

        if ($this->data instanceof LengthAwarePaginator || $this->data instanceof Collection) {
            return $resourceClass::collection($this->data);
        } elseif ($this->data instanceof Model) {
            return new $resourceClass($this->data);
        }

        return response()->json(
            [
                'data' => $this->data,
            ],
            !!$this->data
                ? $this->succesfullRequestCode
                : $this->unsuccesfullRequestCode
        );
    }

    /**
     * @return mixed
     */
    public function unwrap()
    {
        return $this->data;
    }
}
