<?php

namespace CrixuAMG\Decorators;

use Dealmaker\Console\Commands\CacheMakeCommand;
use Dealmaker\Console\Commands\ContractMakeCommand;
use Dealmaker\Console\Commands\RepositoryMakeCommand;
use Dealmaker\Console\Commands\TraitMakeCommand;
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