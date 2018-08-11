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
     * @param AbstractProfile $profile
     *
     * @return HasCacheProfiles
     */
    public function setProfile(AbstractProfile $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return HasCacheProfiles
     */
    public function setDefaultProfile()
    {
        return $this->setProfile(config('decorators.cache.default_profile'));
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        // Check the method exists in this class
        if (method_exists($this, $method)) {
            // If the method exists on the profile, call it
            if (method_exists($this->profile, $method)) {
                call_user_func_array(
                    [
                        $this->profile,
                        $method,
                    ],
                    $arguments
                );
            }

            // The method exists on this class, execute it and return the result
            return call_user_func_array(
                [
                    $this,
                    $method,
                ],
                $arguments
            );
        }
    }
}
