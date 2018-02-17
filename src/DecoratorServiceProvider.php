<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Console\Commands\CacheMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ContractMakeCommand;
use CrixuAMG\Decorators\Console\Commands\RepositoryMakeCommand;
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
        $this->registerCommands();
    }

    /**
     *
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheMakeCommand::class,
                ContractMakeCommand::class,
                RepositoryMakeCommand::class,
            ]);
        }
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