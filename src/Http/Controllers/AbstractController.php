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
    private $succesfulRequestCode = 200;
    /**
     * @var int
     */
    private $unsuccesfulRequestCode = 500;

    /**
     * @param int $succesfulRequestCode
     */
    public function setSuccesfulRequestCode(int $succesfulRequestCode = 200)
    {
        $this->succesfulRequestCode = $succesfulRequestCode;
    }

    /**
     * @param int $unsuccesfulRequestCode
     */
    public function setUnsuccesfulRequestCode(int $unsuccesfulRequestCode = 500)
    {
        $this->unsuccesfulRequestCode = $unsuccesfulRequestCode;
    }

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
        if (
            method_exists($this->repository, $name) &&
            \is_callable([$this->repository, $name])
        ) {
            $this->setData($this->repository->{$name}(...$arguments));

            return $this;
        }
    }

    /**
     * @param null $resourceClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resourceful($resourceClass = null)
    {
        // Try to get the resource class if it is not filled
        if (!$resourceClass) {
            $calledClass   = get_called_class();
            $resourceClass = $this->getResourceClass($calledClass);
        }

        // If the resource class variable is filled, use it when the data is either a collection or a model
        if ($resourceClass) {
            if ($this->data instanceof LengthAwarePaginator || $this->data instanceof Collection) {
                return $resourceClass::collection($this->data);
            } elseif ($this->data instanceof Model) {
                return new $resourceClass($this->data);
            }
        }

        // Guess the response code
        $responseCode = $this->guessStatusCode();

        // Get the response data
        $responseData = $this->getResponseData();

        // Return the response as JSON
        return response()->json(
            $responseData,
            $responseCode
        );
    }

    /**
     * @return mixed
     */
    public function unwrap()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    private function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    private function guessStatusCode(): int
    {
        return !!$this->data
            ? $this->succesfulRequestCode
            : $this->unsuccesfulRequestCode;
    }

    private function getResponseData(): array
    {
        return !!$this->data
            ? [
                'data' => $this->data,
            ]
            : [
                'message' => 'An error occurred while performing the requested action.',
                'errors'  => config('app.env') === 'production'
                    ? []
                    : debug_backtrace(),
            ];
    }

    /**
     * @param $calledClass
     *
     * @return string|null
     */
    private function getResourceClass($calledClass)
    {
        $resourceClass  = null;
        $namespace      = str_before($calledClass, '\\Http');
        $controllerName = str_after($calledClass, 'Controllers\\');
        $controllerName = str_before($controllerName, 'Controller');

        $resourceClassName = sprintf('%s\Http\Resources\%sResource', $namespace, $controllerName);

        if (class_exists($resourceClassName)) {
            $resourceClass = $resourceClassName;
        }

        return $resourceClass;
    }
}
