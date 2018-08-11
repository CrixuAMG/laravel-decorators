<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Profiles\AbstractProfile;

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
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return HasCacheProfiles
     */
    public function setDefaultProfile()
    {
        return $this->setProfile($this->getDefaultProfile());
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
