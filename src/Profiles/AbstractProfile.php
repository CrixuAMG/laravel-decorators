<?php

namespace CrixuAMG\Decorators\Profiles;

use CrixuAMG\Decorators\Contracts\CacheProfileContract;

/**
 * Class AbstractProfile
 * @package CrixuAMG\Decorators\Profiles
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
     * @var array
     */
    protected $defaultTags;

    /**
     * DefaultProfile constructor.
     */
    public function __construct()
    {
        $this->enabled = (bool)config('decorators.cache.enabled');
        $this->time = (int)config('decorators.cache.minutes');
        $this->defaultTags = (array)config('decorators.cache.default_tags');
    }
}
