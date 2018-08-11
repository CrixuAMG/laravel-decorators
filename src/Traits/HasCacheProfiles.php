<?php

namespace CrixuAMG\Decorators\Traits;

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
        if (\is_string($profile) && class_exists($profile)) {
            // Convert the string to an instance of the profile
            $profile = new $profile;
        }

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
