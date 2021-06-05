<?php

namespace CrixuAMG\Decorators\Test\Providers;

use CrixuAMG\Decorators\Decorators\AbstractDecorator;

class TestDecorator extends AbstractDecorator implements TestContract
{
    /**
     * @param int $number
     *
     * @return int
     */
    public function get(int $number): int
    {
        return $this->forwardIfAllowed(__FUNCTION__, true, $number);
    }

    /**
     * @param int $number
     *
     * @return int
     */
    public function getWithoutCacheParameters(int $number): int
    {
        return $this->forwardIfAllowed(__FUNCTION__, true, $number);
    }
}
