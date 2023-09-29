<?php

namespace CrixuAMG\Decorators\Traits;

use Inertia\Inertia;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use CrixuAMG\Decorators\Services\AdditionalResourceData;
use CrixuAMG\Decorators\Services\QueryResult\CountResponse;

/**
 * Trait HasResources
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait HasResources
{
    /**
     * @var
     */
    protected $resource;
    protected $redirect;
    protected $inertia;

    /**
     * @param mixed $data The data to check and make resourceful if possible
     *
     * @return mixed
     */
    public function resourceful($data)
    {
        if ($data instanceof CountResponse) {
            return $data->toResponse();
        }

        if (!request()->wantsJson() || request()->header('X-Inertia')) {
            if (is_callable($this->inertia) && class_exists(Inertia::class)) {
                return Inertia::render(...call_user_func($this->inertia, $data));
            }

            if (is_callable($this->redirect)) {
                return call_user_func($this->redirect, $data);
            }
        }

        if ($this->resource) {
            $resource = $this->getResource();

            if ($data instanceof LengthAwarePaginator || $data instanceof Collection) {
                $data = $resource::collection($data);
            } else if ($data instanceof Model || is_array($data)) {
                $data = new $resource($data);
            }
        }

        if ($data instanceof JsonResource) {
            $data->additional(["response" => AdditionalResourceData::getData()]);
        }

        return $data;
    }

    /**
     * @return mixed|string|null
     */
    public function getResource()
    {
        if (is_string($this->resource)) {
            return $this->resource;
        }

        if (is_array($this->resource)) {
            $requestedResource = request()->__resource ?? 'default';
            $resource = null;

            if (isset($this->resource[$requestedResource])) {
                $resource = $this->resource[$requestedResource];
            } else if (isset($this->resource['default'])) {
                $resource = $this->resource['default'];
            }

            abort_unless($resource, 422);
        }

        return $resource;
    }

    /**
     * @param mixed $resource
     *
     * @return mixed
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    public function withRedirect(callable $callback)
    {
        $this->redirect = $callback;

        return $this;
    }

    public function inertia(callable $callback)
    {
        $this->inertia = $callback;

        return $this;
    }
}
