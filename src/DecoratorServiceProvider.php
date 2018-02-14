<?php

namespace CrixuAMG\Decorators;

use Illuminate\Support\ServiceProvider;

/**
 * Class DecoratorServiceProvider
 *
 * @package CrixuAMG
 */
class DecoratorServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function boot()
    {
        //
    }

    /**
     *
     */
    public function register()
    {
        $this->app->bind('decorator', function ($app) {
            return new Decorator;
        });
    }
}