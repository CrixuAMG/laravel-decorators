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
     * @throws \Throwable
     */
    public function register()
    {
        // Create our instance
        $this->app->singleton(
            Handler::class,
            function () {
                return new Handler($this->app);
            }
        );
    }
}