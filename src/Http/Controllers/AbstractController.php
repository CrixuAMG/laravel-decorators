<?php

namespace CrixuAMG\Decorators\Http\Controllers;

use CrixuAMG\Decorators\Traits\HasForwarding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use ShareFeed\Http\Controllers\Controller;

/**
 * Class AbstractController
 *
 * @package CrixuAMG\Decorators\Http\Controllers
 */
abstract class AbstractController extends Controller
{
    use HasForwarding;
    /**
     * @var
     */
    protected $next;

    /**
     * @var
     */
    protected $resource;

    /**
     * @param mixed $next
     *
     * @return AbstractController
     */
    public function setNext($next)
    {
        $this->next = $next;

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

    /**
     * @param string $method
     * @param mixed  ...$args
     *
     * @return mixed
     */
    public function forwardResourceful(string $method, ...$args)
    {
        $result = $this->forward($method, ...$args);

        if ($this->resource) {
            if ($result instanceof LengthAwarePaginator || $result instanceof Collection) {
                $result = $this->resource::collection($result);
            } elseif ($result instanceof Model) {
                $result = new $this->resource($result);
            }
        }

        return $result;
    }
}
