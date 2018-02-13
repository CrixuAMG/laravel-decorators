<?php

namespace CrixuAMG/Decorators;

use Illuminate\Support\ServiceProvider;

class DecoratorServiceProvide extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this-app->bind('decorator', function($app) {
            return new Decorator;
        });
    }
}