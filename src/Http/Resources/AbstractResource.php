<?php

namespace CrixuAMG\Decorators\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class AbstractResource
 * @package CrixuAMG\Decorators\Http\Resources
 */
class AbstractResource extends JsonResource
{
    /**
     * @param array $data
     * @return array
     */
    public function format(array $data, string $trimKey = '')
    {
//        return $data;
        return $this->filterData($this->getFilters($trimKey), $data);
    }

    /**
     * @param string $trimKey
     * @return array
     */
    public function getFilters(string $trimKey): array
    {
        $only = [];

        if (!empty(request()->only)) {
            $onlyList = json_decode(request()->only, true);
            if (!empty($onlyList) && is_array($onlyList)) {
                foreach ($onlyList as $index => $keyToUnset) {
                    $onlyList[$index] = Str::after($keyToUnset, $trimKey);
                    $value            = Arr::last(explode('.', $onlyList[$index]));

                    Arr::set($only, $onlyList[$index], $value);
                }
            }
        }

        return $only;
    }

    /**
     * @param array $only
     * @param array $data
     * @return array
     */
    public function filterData(array $only, $data): array
    {
        if (!empty($only)) {
            $newDataSet = [];

            foreach ($only as $key => $value) {
                $returnedValue = new \stdClass();

                if (is_array($value)) {
                    $returnedValue = $this->filterData($value, $data[$key]);
                } else if (is_array($data) && in_array($key, $data)) {
                    if (!is_array($data[$key])) {
                        $returnedValue = $data[$key];
                    } else {
                        foreach ($value as $subKey => $dataSet) {
                            $returnedValue = $this->filterData($value, $data[$key]);
                        }
                    }
                } else {
                    if ($data instanceof Collection || $data instanceof AnonymousResourceCollection) {
                        $returnedValue = [];

                        foreach ($data as $subObjectIndex => $subObject) {
                            $returnedValue[] = $this->filterData($only, $subObject);
                        }
                    } else if (is_object($data) && !empty($data->resource->toArray())) {
                        $returnedValue = $this->filterData($only, $data->resource->toArray());
                    } else {
                        dd($data);
                        throw new \Exception('Unsupported data format ' . gettype($data));
                    }
                }

                if (!$returnedValue instanceof \stdClass) {
                    $newDataSet[$key] = $returnedValue;
                }
            }

            $data = $newDataSet;
        }

        return $data;
    }
}
