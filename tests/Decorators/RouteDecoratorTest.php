<?php

namespace CrixuAMG\Decorators\Test\Decorators;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Decorator;
use CrixuAMG\Decorators\Exceptions\RouteDecoratorMatchMissingException;
use CrixuAMG\Decorators\Test\Providers\TestCache;
use CrixuAMG\Decorators\Test\Providers\TestContract;
use CrixuAMG\Decorators\Test\Providers\TestDecorator;
use CrixuAMG\Decorators\Test\Providers\TestRepository;
use CrixuAMG\Decorators\Test\TestCase;

/**
 * Class RouteDecoratorTest
 * @package CrixuAMG\Decorators\Test\Decorators
 */
class RouteDecoratorTest extends TestCase
{
    /**
     * @var Decorator
     */
    private $decorator;

    /**
     * @throws RouteDecoratorMatchMissingException
     */
    public function setUp()
    {
        parent::setUp();

        config([
            'decorators.ignored_routes' => [
                '',
                '/',
            ],
        ]);

        Cache::enabled(true);
        $this->decorator = app(Decorator::class);
        $this->decorator->autoregisterRoute();
    }

    /**
     * @ test
     */
    public function a_route_can_be_decorated()
    {
        config([
            'decorators.route_matchables' => [
                'decorators' => [
                    '__contract'  => TestContract::class,
                    '__arguments' => [
                        TestRepository::class,
                        TestCache::class,
                        TestDecorator::class,
                    ],
                ],
            ],
        ]);

        $response = $this->getJson('/decorators/test');

        dump($response->getContent());

        $response->assertSuccessful()
            ->assertJson([
                'data' => [
                    'message' => 'All ok!',
                ],
            ]);
    }

    /**
     * @ test
     */
    public function an_exception_is_thrown_when_no_match_can_be_found()
    {
        $this->expectException(RouteDecoratorMatchMissingException::class);
        $this->getJson('/test/exception');
        $this->getExpectedException();
    }
}
