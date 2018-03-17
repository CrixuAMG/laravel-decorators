<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Console\Commands\CacheMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ContractMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DecoratorMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DecoratorsMakeCommand;
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
        // Register the commands
        $this->registerCommands();

        // Allow the user to get the config file
        $this->registerConfiguration();
    }

    /**
     * Register console commands
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheMakeCommand::class,        // make:cache
                ContractMakeCommand::class,     // make:contract
                RepositoryMakeCommand::class,   // make:repository
                DecoratorMakeCommand::class,    // make:decorator
                DecoratorsMakeCommand::class,   // decorators:make
            ]);
        }
    }

    /**
     * Register the config file
     */
    private function registerConfiguration()
    {
        $this->publishes([
            __DIR__ . '/config/decorators.php' => config_path('decorators.php'),
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function register()
    {
        // Create our instance
        $this->app->singleton(
            Decorator::class,
            function () {
                return new Decorator();
            }
        );
    }
}