<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Services\AdditionalResourceData;
use CrixuAMG\Decorators\Services\QueryResult\CountResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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

    /**
     * @param  mixed  $data  The data to check and make resourceful if possible
     *
     * @return mixed
     */
    public function resourceful($data)
    {
        if ($data instanceof CountResponse) {
            return $data->toResponse();
        }

        if ($this->resource) {
            $resource = $this->getResource();

            if ($data instanceof LengthAwarePaginator || $data instanceof Collection) {
                $data = $resource::collection($data);
            } elseif ($data instanceof Model || is_array($data)) {
                $data = new $resource($data);
            }
        }

        if ($data instanceof JsonResource ) {
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
            } elseif (isset($this->resource['default'])) {
                $resource = $this->resource['default'];
            }

            abort_unless($resource, 422);
        }

        return $resource;
    }

    /**
     * @param  mixed  $resource
     *
     * @return mixed
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }
}
