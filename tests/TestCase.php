<?php

namespace CrixuAMG\Decorators\Test;

use CrixuAMG\Decorators\Test\Providers\TestContract;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Class TestCase
 * @package CrixuAMG\Decorators\Test
 */
abstract class TestCase extends Orchestra
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpRoutes();
    }

    public function setUpRoutes()
    {
        Route::any('/decorators/test', function () {
            // Create a new instance to know that it worked
            return app(TestContract::class);

            return response()->json(
                [
                    'data' => [
                        'message' => 'All ok!',
                    ],
                ],
                200
            );
        });
        Route::any('/test/exception', function () {
            // This should not work
            app(TestContract::class);

            return response()->json(
                [
                    'data' => [
                        'message' => 'This message should not get returned!',
                    ],
                ],
                500
            );
        });
    }
}
