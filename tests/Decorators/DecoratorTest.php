<?php

namespace CrixuAMG\Decorators\Test\Decorators;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Decorator;
use CrixuAMG\Decorators\Exceptions\InterfaceNotImplementedException;
use CrixuAMG\Decorators\Test\Providers\TestCache;
use CrixuAMG\Decorators\Test\Providers\TestContract;
use CrixuAMG\Decorators\Test\Providers\TestDecorator;
use CrixuAMG\Decorators\Test\Providers\TestRepository;
use CrixuAMG\Decorators\Test\TestCase;
use Mockery\Mock;

/**
 * Class DecoratorTest
 *
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
    public function setUp(): void
    {
        parent::setUp();

        Cache::enabled(true);
        $this->decorator = app(Decorator::class);
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
    }

    /**
     * @test
     * @depends it_can_decorate_repositories
     * @depends it_can_decorate_caches
     * @depends it_can_decorate_decorators
     */
    public function an_exception_is_thrown_when_a_class_is_passed_that_does_not_implement_the_contract(): void
    {
        $this->decorator->decorate(TestContract::class, [
            TestRepository::class,
            Mock::class,
        ]);

        $this->expectException(InterfaceNotImplementedException::class);
        app(TestContract::class);
        $this->getExpectedException();
    }
}
