<?php

namespace CrixuAMG\Decorators\Test\Decorators;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Decorator;
use CrixuAMG\Decorators\Test\Providers\TestCache;
use CrixuAMG\Decorators\Test\Providers\TestContract;
use CrixuAMG\Decorators\Test\Providers\TestDecorator;
use CrixuAMG\Decorators\Test\Providers\TestRepository;
use CrixuAMG\Decorators\Test\TestCase;

/**
 * Class DecoratorTest
 * @package CrixuAMG\Decorators\Test\Decorators
 */
class DecoratorTest extends TestCase
{
    /**
     * @var
     */
    private $decorator;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->setDecorator();
    }

    /**
     * @test
     */
    public function it_can_decorate_repositories(): void
    {
        $this->decorator->decorate(TestContract::class, [
            TestRepository::class,
        ]);

        $instance = app(TestContract::class);

        $this->assertInstanceOf(TestRepository::class, $instance);
        $this->setDecorator();
    }

    /**
     * @test
     * @depends it_can_decorate_repositories
     */
    public function it_can_decorate_caches(): void
    {
        $this->decorator->decorate(TestContract::class, [
            TestRepository::class,
            TestCache::class,
        ]);

        $instance = app(TestContract::class);

        $this->assertInstanceOf(TestCache::class, $instance);
        $this->setDecorator();
    }

    /**
     * @test
     * @depends it_can_decorate_repositories
     * @depends it_can_decorate_caches
     */
    public function it_can_decorate_decorators(): void
    {
        $this->decorator->decorate(TestContract::class, [
            TestRepository::class,
            TestCache::class,
            TestDecorator::class,
        ]);

        $instance = app(TestContract::class);

        $this->assertInstanceOf(TestDecorator::class, $instance);
        $this->setDecorator();
    }

    /**
     *
     */
    private function setDecorator(): void
    {
        Cache::enabled(true);
        $this->decorator = null;
        $this->decorator = app(Decorator::class);
    }
}
