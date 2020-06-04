<?php

namespace CrixuAMG\Decorators\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
    public function format(array $data)
    {
        if (!empty(request()->only)) {
            $only = json_decode(request()->only, true);

            if (!empty($only) && is_array($only)) {
                foreach ($only as $keyToUnset) {
                    $this->recursiveUnset($data, $keyToUnset);
                }
            }
        }

        return $data;
    }

    /**
     * @param $array
     * @param $unwanted_key
     */
    private function recursiveUnset(&$array, $unwanted_key) {
        unset($array[$unwanted_key]);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveUnset($value, $unwanted_key);
            }
        }
    }
}
