<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Console\Commands\CacheMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ContractMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ControllerMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DecoratorMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DecoratorsMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DefinitionMakeCommand;
use CrixuAMG\Decorators\Console\Commands\MakeStarterCommand;
use CrixuAMG\Decorators\Console\Commands\ObserverMakeCommand;
use CrixuAMG\Decorators\Console\Commands\RepositoryMakeCommand;
use CrixuAMG\Decorators\Console\Commands\RuleMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ScopeMakeCommand;
use CrixuAMG\Decorators\Console\Commands\TraitMakeCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Class DecoratorServiceProvider
 *
 * @package CrixuAMG
 *
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

        $this->registerMacros();
    }

    /**
     * Register console commands
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheMakeCommand::class,
                // decorators:cache
                ContractMakeCommand::class,
                // decorators:contract
                RepositoryMakeCommand::class,
                // decorators:repository
                DecoratorMakeCommand::class,
                // decorators:decorator
                ControllerMakeCommand::class,
                // decorators:controller
                MakeStarterCommand::class,
                // decorators:starter
                TraitMakeCommand::class,
                // decorators:trait
                ObserverMakeCommand::class,
                // decorators:observer
                ScopeMakeCommand::class,
                // decorators:scope
                DefinitionMakeCommand::class,
                // decorators:definition
                DecoratorsMakeCommand::class,
                // decorators:make
                RuleMakeCommand::class,
                // decorators:rule
            ]);
        }
    }

    /**
     * Register the config file
     */
    private function registerConfiguration()
    {
        $this->publishes([
            __DIR__.'/config/decorators.php' => config_path('decorators.php'),
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
                return new Decorator($this->app);
            }
        );
    }

    private function registerMacros()
    {
        Route::macro(
            'definition',
            function (string $controller, string $routePath = 'definition', string $method = 'definition') {
                return Route::get($routePath, [$controller, $method]);
            }
        );
    }
}
