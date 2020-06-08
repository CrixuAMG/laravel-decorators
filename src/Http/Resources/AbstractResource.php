<?php

namespace CrixuAMG\Decorators\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
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
        $only = $this->getFilters($trimKey);

        return $this->filterData($only, $data);
    }

    /**
     * @param string $trimKey
     * @return array
     */
    public function getFilters(string $trimKey): array
    {
        $only = [];

        if (!empty(request()->only)) {
            $only = json_decode(request()->only, true);
            if (!empty($only) && is_array($only)) {
                foreach ($only as $index => $keyToUnset) {
                    $only[$index] = Str::after($keyToUnset, $trimKey);
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
    public function filterData(array $only, array $data): array
    {
        if (!empty($only)) {
            $newDataSet = [];

            foreach ($data as $key => $value) {
                $returnedValue = null;

                if (is_array($value)) {
                    $returnedValue = Arr::only($value, $only);
                } else if (in_array($key, $only)) {
                    $returnedValue = $value;
                }

                if ($returnedValue !== null) {
                    $newDataSet[$key] = $returnedValue;
                }
            }

            $data = $newDataSet;
        }

        return $data;
    }
}
