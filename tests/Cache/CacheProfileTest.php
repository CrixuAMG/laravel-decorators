<?php

namespace CrixuAMG\Decorators\Test\CacheProfiles;

use CrixuAMG\Decorators\Profiles\DefaultProfile;
use CrixuAMG\Decorators\Test\TestCase;

class CacheProfileTest extends TestCase
{
    private $cacheProfile;

    public function setUp()
    {
        parent::setUp();

        $this->cacheProfile = app(DefaultProfile::class);
    }

    /**
     * @test
     */
    public function it_can_set_time()
    {
        $this->assertEquals(30, $this->cacheProfile->time(30));
    }

    /**
     * @test
     */
    public function it_can_be_enabled()
    {
        $this->assertEquals(true, $this->cacheProfile->enabled(true));
    }

    /**
     * @test
     */
    public function it_can_be_disabled()
    {
        $this->assertEquals(false, $this->cacheProfile->enabled(false));
    }
}
