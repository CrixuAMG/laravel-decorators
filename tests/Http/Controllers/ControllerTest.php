<?php

namespace CrixuAMG\Decorators\Test\Http\Controllers;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Decorator;
use CrixuAMG\Decorators\Test\Http\Resources\TestResource;
use CrixuAMG\Decorators\Test\Providers\TestDecorator;
use CrixuAMG\Decorators\Test\Providers\TestModel;
use CrixuAMG\Decorators\Test\TestCase;
use Illuminate\Database\Eloquent\Collection;

class ControllerTest extends TestCase
{
    private $decorator;
    private $controller;

    public function setUp(): void
    {
        parent::setUp();

        Cache::enabled(true);
        $this->decorator = app(Decorator::class);
    }

    /**
     * @return array[]
     */
    public function controllerDataProvider(): array
    {
        return [
            [
                TestController::class,
            ],
        ];
    }

    /** @test */
    public function the_application_can_be_setup()
    {
        $this->controller = app(TestController::class);

        $this->assertInstanceOf(TestDecorator::class, $this->controller->next);
    }

    /**
     * @param  string  $controller
     *
     * @test
     * @depends      the_application_can_be_setup
     * @dataProvider controllerDataProvider
     */
    public function the_index_method_can_be_called(string $controller)
    {
        $this->assertInstanceOf(
            \Illuminate\Http\Resources\Json\AnonymousResourceCollection::class,
            (new $controller())->index()
        );

        $this->assertInstanceOf(
            Collection::class,
            (new $controller())->forwardWithCallback('index', fn ($data) => $data)
        );

        $this->assertInstanceOf(
            Collection::class,
            (new $controller())->forwardCachedCallback('index', fn ($data) => $data)
        );
    }

    /**
     * @param  string  $controller
     *
     * @test
     * @depends      the_application_can_be_setup
     * @dataProvider controllerDataProvider
     */
    public function the_show_method_can_be_called(string $controller)
    {
        $this->assertInstanceOf(
            TestResource::class,
            (new $controller())->show(new TestModel())
        );
    }

    /**
     * @param  string  $controller
     *
     * @test
     * @depends      the_application_can_be_setup
     * @dataProvider controllerDataProvider
     */
    public function the_store_method_can_be_called(string $controller)
    {
        $this->assertInstanceOf(
            TestResource::class,
            (new $controller())->store([
                'foo' => 'bar',
            ])
        );
    }

    /**
     * @param  string  $controller
     *
     * @test
     * @depends      the_application_can_be_setup
     * @dataProvider controllerDataProvider
     */
    public function the_update_method_can_be_called(string $controller)
    {
        $this->assertInstanceOf(
            TestResource::class,
            (new $controller())->update(
                new TestModel(),
                [
                    'foo' => 'bar',
                ]
            )
        );
    }

    /**
     * @param  string  $controller
     *
     * @test
     * @depends      the_application_can_be_setup
     * @dataProvider controllerDataProvider
     */
    public function the_destroy_method_can_be_called(string $controller)
    {
        $this->assertTrue(
            (new $controller())->destroy(new TestModel())
        );
    }

    /**
     * @param  string  $controller
     *
     * @test
     * @depends      the_application_can_be_setup
     * @dataProvider controllerDataProvider
     */
    public function the_get_method_can_be_called(string $controller)
    {
        $this->assertEquals(
            3,
            (new $controller())->get(3)
        );
    }

    /**
     * @param  string  $controller
     *
     * @test
     * @depends      the_application_can_be_setup
     * @dataProvider controllerDataProvider
     */
    public function the_get_without_cache_parameters_method_can_be_called(string $controller)
    {
        $this->assertEquals(
            3,
            (new $controller())->getWithoutCacheParameters(3)
        );
    }
}
