<?php

namespace CrixuAMG\Decorators\Test\CacheProfiles;

use CrixuAMG\Decorators\Profiles\DefaultProfile;
use CrixuAMG\Decorators\Test\TestCase;

/**
 * Class CacheProfileTest
 * @package CrixuAMG\Decorators\Test\CacheProfiles
 */
class CacheProfileTest extends TestCase
{
    /**
     * @var
     */
    private $cacheProfile;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->cacheProfile = app(DefaultProfile::class);
    }

    /**
     * @test
     * @dataProvider timeProvider
     *
     * @param int $expected
     * @param int $given
     */
    public function it_can_set_time(int $expected, int $given)
    {
        $this->assertEquals($expected, $this->cacheProfile->time($given));
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

    /**
     * @return array
     */
    public function timeProvider()
    {
        return [
            [
                30,
                30,
            ],
            [
                60,
                60,
            ],
            [
                15,
                15,
            ],
        ];
    }
}
