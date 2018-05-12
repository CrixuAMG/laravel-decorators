<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use CrixuAMG\Decorators\Traits\Forwardable;
use ShareFeed\Http\Controllers\Controller;

/**
 * Class AbstractController
 *
 * @package CrixuAMG\Decorators\Http\Controllers
 */
abstract class AbstractController extends Controller
{
    use Forwardable;
    /**
     * @var
     */
    protected $repository;

    /**
     * @var
     */
    protected $resource;

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
     * @param mixed $resource
     *
     * @return AbstractController
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }
}
