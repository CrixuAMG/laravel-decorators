<?php

namespace CrixuAMG\Decorators\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Trait HasResources
 * @package CrixuAMG\Decorators\Traits
 */
trait HasResources
{
    /**
     * @var
     */
    protected $resource;

    /**
     * @param mixed $resource
     *
     * @return HasResources
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function resourceful($data)
    {
        if ($this->resource) {
            if ($data instanceof LengthAwarePaginator || $data instanceof Collection) {
                $data = $this->resource::collection($data);
            } elseif ($data instanceof Model) {
                $data = new $this->resource($data);
            }
        }

        return $data;
    }
}
