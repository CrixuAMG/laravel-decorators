<?php

namespace CrixuAMG\Decorators\Test\Cache;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Decorator;
use CrixuAMG\Decorators\Test\Providers\TestCache;
use CrixuAMG\Decorators\Test\Providers\TestContract;
use CrixuAMG\Decorators\Test\Providers\TestDecorator;
use CrixuAMG\Decorators\Test\Providers\TestRepository;
use CrixuAMG\Decorators\Test\TestCase;
use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\HasForwarding;

/**
 * Class CacheLogicTest
 *
 * @package CrixuAMG\Decorators\Test\Cache
 */
class CacheLogicTest extends TestCase
{
    use HasForwarding, HasCaching;
    /**
     * @var TestDecorator
     */
    private $instance;
    /**
     * @var Decorator
     */
    private $decorator;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        Cache::enabled(true);
        Cache::time(60);
        $this->decorator = app(Decorator::class);
        $this->decorator->decorate(TestContract::class, [
            TestRepository::class,
            TestCache::class,
        ]);

        $this->instance = app(TestContract::class);

        $this->setNext($this->instance);
    }

    /**
     * @test
     */
    public function it_can_send_data_and_it_returns_the_same(): void
    {
        $this->assertEquals(2, $this->forward('get', 2));
    }

    /**
     * @test
     */
    public function when_the_same_method_is_called_twice_the_correct_value_is_returned(): void
    {
        $this->assertEquals(3, $this->forward('get', 3));
        $this->assertEquals(4, $this->forward('get', 4));
    }

    /**
     * @test
     */
    public function when_cache_parameters_are_not_defined_it_returns_the_cached_value()
    {
        $this->assertEquals(3, $this->forward('getWithoutCacheParameters', 3));
        $this->assertNotEquals(4, $this->forward('getWithoutCacheParameters', 4));
    }
}
