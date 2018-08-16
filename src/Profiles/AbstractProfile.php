<?php

namespace CrixuAMG\Decorators\Profiles;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Contracts\CacheProfileContract;

/**
 * Class AbstractProfile
 * @package CrixuAMG\Decorators\CacheProfiles
 */
abstract class AbstractProfile implements CacheProfileContract
{
    /**
     * @var int
     */
    protected $time;
    /**
     * @var bool
     */
    protected $enabled;

    /**
     * DefaultProfile constructor.
     */
    public function __construct()
    {
        $this->enabled = Cache::enabled();
        $this->time = Cache::time();
    }
}
