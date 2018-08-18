<?php

namespace CrixuAMG\Decorators\Test\Decorators;

use CrixuAMG\Decorators\Test\TestCase;
use Illuminate\Http\Request;

class RouteDecoratorTest extends TestCase
{
    private $route;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->route = '/decorators/first/test';

        Request::create($this->route, 'GET');
    }

    /**
     * @test
     */
    public function a_route_can_be_decorated()
    {
//        $response = $this->getJson($this->route);
//
//        $response->assertSuccessful();
    }
}
