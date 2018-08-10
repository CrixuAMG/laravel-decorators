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
    public function setProfile(AbstractProfile $profile): HasCacheProfiles
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return HasCacheProfiles
     */
    public function setDefaultProfile(): HasCacheProfiles
    {
        return $this->setProfile(config('decorators.cache.default_profile'));
    }
}
