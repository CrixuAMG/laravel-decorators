<?php

namespace CrixuAMG\Decorators\Services;

/**
 *
 */
class AdditionalResourceData
{
    /**
     * @var
     */
    private static $data;

    /**
     * @return array
     */
    public static function getData():array
    {
        return self::$data ?: [];
    }

    /**
     * @param  string  $key
     * @param $value
     */
    public static function addData(string $key, $value): void
    {
        if (!is_array(self::$data)) {
            self::$data = [];
        }

        self::$data[$key] = $value;
    }

    /**
     * @param  string  $key
     * @param $value
     */
    public static function appendData(string $key, $value): void
    {
        if (!isset(self::$data[$key])) {
            self::$data[$key] = [];
        }

        self::$data[$key][] = $value;
    }
}
