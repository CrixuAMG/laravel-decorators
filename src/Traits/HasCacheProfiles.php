<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Caches\Cache;

/**
 * Trait HasCacheProfiles
 * @package CrixuAMG\Decorators\Traits
 */
trait HasCacheProfiles
{
    /**
     * @var
     */
    protected $profile;

    /**
     * @param $profile
     *
     * @return HasCacheProfiles
     */
    public function profile($profile = null)
    {
        return Cache::profile($profile);
    }

    /**
     * @return HasCacheProfiles
     */
    public function setDefaultProfile()
    {
        return $this->profile($this->getDefaultProfile());
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getDefaultProfile()
    {
        return config('decorators.cache.default_profile');
    }

    /**
     * @param string $method
     * @param mixed  ...$args
     */
    public function callProfileMethod(string $method, ...$args)
    {
        // If the method exists on the profile, call it
        if (method_exists($this->profile, $method)) {
            $this->profile->{$method}(...$args);
        }
    }
}
