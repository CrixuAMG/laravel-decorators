<?php

namespace CrixuAMG\Decorators\Test\Providers;

use CrixuAMG\Decorators\Caches\AbstractCache;

class TestCache extends AbstractCache implements TestContract
{
    /**
     * @param int $number
     *
     * @return int
     */
    public function get(int $number): int
    {
        return $this->setCacheParameters([
            $number,
        ])
            ->forwardCached(__FUNCTION__, $number);
    }

    /**
     * @param int $number
     *
     * @return int
     */
    public function getWithoutCacheParameters(int $number): int
    {
        return $this->forwardCached(__FUNCTION__, $number);
    }
}
