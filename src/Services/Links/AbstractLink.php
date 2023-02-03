<?php

namespace CrixuAMG\Decorators\Services\Links;

use Illuminate\Contracts\Support\Jsonable;
use CrixuAMG\Decorators\Contracts\LinkContract;

abstract class AbstractLink implements LinkContract, Jsonable
{
    private array $data;

    /**
     * > This function takes an array of data and assigns it to the data property of the class
     *
     * @param array $data The data to be used in the template.
     *
     * @return LinkContract The object itself.
     */
    public function __construct(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * It creates a new instance of the class.
     *
     * @param array $data The data to be used to create the model.
     *
     * @return static A new instance of the class.
     */
    public static function create(array $data): LinkContract
    {
        return new static($data);
    }

    /**
     * It returns a string representation of the object.
     *
     * @return string The json_encode() function returns the JSON representation of a value.
     */
    public function __toString(): string
    {
        return json_encode($this->toJson());
    }

    /**
     * It returns the data.
     *
     * @param int $options
     *
     * @return array The data property of the object.
     */
    public function toJson($options = 0): array
    {
        return $this->data;
    }

    /**
     * It sets the value of the data array.
     *
     * @param string $name  The name of the variable to set.
     * @param mixed  $value The value to set.
     *
     * @return LinkContract The object itself.
     */
    public function set(string $name, mixed $value = null): LinkContract
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * > This function returns the value of the key in the array, or null if the key doesn't exist
     *
     * @param string $name The name of the parameter to get.
     *
     * @return mixed The value of the key in the array.
     */
    public function get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }
}
