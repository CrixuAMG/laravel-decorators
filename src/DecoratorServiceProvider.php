<?php

namespace CrixuAMG\Decorators;

use Throwable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use CrixuAMG\Decorators\Services\Module\Module;
use CrixuAMG\Decorators\Console\Commands\RuleMakeCommand;
use CrixuAMG\Decorators\Http\Resource\DefinitionResource;
use CrixuAMG\Decorators\Console\Commands\CacheMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ScopeMakeCommand;
use CrixuAMG\Decorators\Console\Commands\TraitMakeCommand;
use CrixuAMG\Decorators\Console\Commands\MakeStarterCommand;
use CrixuAMG\Decorators\Console\Commands\ContractMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ObserverMakeCommand;
use CrixuAMG\Decorators\Console\Commands\PublishStubsCommand;
use CrixuAMG\Decorators\Console\Commands\RouteFileMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DecoratorMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ControllerMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DecoratorsMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DefinitionMakeCommand;
use CrixuAMG\Decorators\Console\Commands\RepositoryMakeCommand;
use CrixuAMG\Decorators\Console\Commands\MovePackageToAnotherModuleCommand;

/**
 * Class DecoratorServiceProvider
 *
 * @package CrixuAMG
 *
 */
class DecoratorServiceProvider extends ServiceProvider
{
    /**
     * @throws Throwable
     */
    public function register()
    {
        // Create our instance
        $this->app->singleton(
            Decorator::class,
            function () {
                return new Decorator($this->app);
            },
        );

        $this->app->singleton(
            Module::class,
            function () {
                return new Module($this->app);
            },
        );

        Module::boot();
    }

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
        $this->commands([
            // decorators:cache
            CacheMakeCommand::class,
            // decorators:contract
            ContractMakeCommand::class,
            // decorators:repository
            RepositoryMakeCommand::class,
            // decorators:decorator
            DecoratorMakeCommand::class,
            // decorators:controller
            ControllerMakeCommand::class,
            // decorators:starter
            MakeStarterCommand::class,
            // decorators:trait
            TraitMakeCommand::class,
            // decorators:observer
            ObserverMakeCommand::class,
            // decorators:scope
            ScopeMakeCommand::class,
            // decorators:definition
            DefinitionMakeCommand::class,
            // decorators:make
            DecoratorsMakeCommand::class,
            // decorators:rule
            RuleMakeCommand::class,
            // decorators:route-file
            RouteFileMakeCommand::class,

            // decorators:move
            MovePackageToAnotherModuleCommand::class,

            // decorators:stubs
            PublishStubsCommand::class,
        ]);
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

    private function registerMacros()
    {
        Route::macro(
            'definition',
            function (string $routePath, string $configPathOrDefinition) {
                return Route::get($routePath, function () use ($configPathOrDefinition) {
                    $configPathOrDefinition = class_exists($configPathOrDefinition)
                        ? $configPathOrDefinition
                        : config(sprintf("decorators.tree.%s.definition", $configPathOrDefinition));

                    return class_exists($configPathOrDefinition)
                        ? new DefinitionResource(new $configPathOrDefinition)
                        : abort(500);
                });
            },
        );
    }
}
