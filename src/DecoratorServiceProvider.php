<?php

namespace CrixuAMG\Decorators;

use CrixuAMG\Decorators\Caches\Cache;
use CrixuAMG\Decorators\Caches\CacheProfile;
use CrixuAMG\Decorators\Console\Commands\CacheMakeCommand;
use CrixuAMG\Decorators\Console\Commands\CacheProfileMakeCommand;
use CrixuAMG\Decorators\Console\Commands\ContractMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DecoratorMakeCommand;
use CrixuAMG\Decorators\Console\Commands\DecoratorsMakeCommand;
use CrixuAMG\Decorators\Console\Commands\RepositoryMakeCommand;
use CrixuAMG\Decorators\Traits\HasCacheProfiles;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

/**
 * Class DecoratorServiceProvider
 *
 * @package CrixuAMG
 */
class DecoratorServiceProvider extends ServiceProvider
{
    use HasCacheProfiles;

    /**
     *
     */
    public function boot()
    {
        // Register the commands
        $this->registerCommands();

        // Allow the user to get the config file
        $this->registerConfiguration();

        // Register macros
        $this->registerMacros();
    }

    /**
     * Register console commands
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheProfileMakeCommand::class,
                // decorators:profile
                CacheMakeCommand::class,
                // decorators:cache
                ContractMakeCommand::class,
                // decorators:contract
                RepositoryMakeCommand::class,
                // decorators:repository
                DecoratorMakeCommand::class,
                // decorators:decorator
                DecoratorsMakeCommand::class,
                // decorators:make
            ]);
        }
    }

    /**
     * Register macros
     */
    private function registerMacros()
    {
        Request::macro('cacheProfile', function ($profile = null) {
            $profile = Cache::profile($profile);

            return new $profile;
        });
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
                return new Decorator($this->app);
            }
        );
    }
}
