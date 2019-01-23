<?php

namespace CrixuAMG\Decorators\Test\Providers;

use CrixuAMG\Decorators\Contracts\DecoratorContract;

/**
 * Interface TestContract
 *
 * @package CrixuAMG\Decorators\Test\Providers
 */
interface TestContract extends DecoratorContract
{
    /**
     * @param int $number
     *
     * @return int
     */
    public function get(int $number): int;

    /**
     * @param int $number
     *
     * @return int
     */
    public function getWithoutCacheParameters(int $number): int;

}
