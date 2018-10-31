<?php

namespace CrixuAMG\Decorators\Test\Cache;

use CrixuAMG\Decorators\Caches\CacheKey;
use CrixuAMG\Decorators\Test\TestCase;

/**
 * Class CacheKeyTest
 * @package CrixuAMG\Decorators\Test\Cache
 */
class CacheKeyTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @dataProvider cacheKeyProvider
     *
     * @param string $format
     * @param array  $parameters
     * @param string $expected
     */
    public function it_can_create_a_key(string $format, array $parameters, string $expected)
    {
        $this->assertEquals($expected, CacheKey::fromFormat($format, $parameters));
    }

    /**
     * @test
     * @dataProvider cacheKeyGenerateProvider
     *
     * @param array  $data
     * @param string $expected
     */
    public function it_can_generate_a_key(array $data, string $expected)
    {
        $this->assertEquals($expected, CacheKey::generate(...$data));
    }

    /**
     * @return array
     */
    public function cacheKeyGenerateProvider()
    {
        return [
            [
                [
                    'bar.foo',
                    'user',
                    1,
                    'page',
                    2,
                ],
                'd8059ab2c7aaec755b492961e8145c58',
            ],
            [
                [
                    'foo.bar',
                    'user',
                    99,
                    'page',
                    10,
                ],
                '682163b016e7bf03bfc78ff035067dab',
            ],
        ];
    }

    /**
     * @return array
     */
    public function cacheKeyProvider()
    {
        return [
            [
                '%s.%s.%u.%s.%u',
                [
                    'foo.index',
                    'user_id',
                    1,
                    'page',
                    1,
                ],
                '8cc7cd9254c8b4ba99b1baf36af2ff47',
            ],
            [
                '%s.%s.%u',
                [
                    'foo.show',
                    'user_id',
                    1,
                ],
                '965424a4e845b4cbdd02fc67592f9c15',
            ],
        ];
    }
}
