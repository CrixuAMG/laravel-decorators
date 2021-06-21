<?php

namespace CrixuAMG\Decorators\Test\Providers;

/**
 * Interface TestContract
 *
 * @package CrixuAMG\Decorators\Test\Providers
 */
interface TestContract
{
    /**
     * @param  int  $number
     *
     * @return int
     */
    public function get(int $number): int;

    /**
     * @param  int  $number
     *
     * @return int
     */
    public function getWithoutCacheParameters(int $number): int;

}
