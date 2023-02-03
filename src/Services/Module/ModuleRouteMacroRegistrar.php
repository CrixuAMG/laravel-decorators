<?php

namespace CrixuAMG\Decorators\Services\Module;

use Closure;
use Illuminate\Support\Facades\Route;

class ModuleRouteMacroRegistrar
{
    public static function register()
    {
        Route::macro('module', function (string $name, Closure|string|array $routes = null, array $options = []) {
            if (func_num_args() === 1) {
                $routes = $name;
            }
            if (func_num_args() === 2 && is_array($routes)) {
                $options = $routes;
                $routes = $name;
            }

            Route::group([
                ...$options,
            ], function () use ($routes) {
                if (is_callable($routes)) {
                    $routes();
                } else {
                    $fullRoutePath = sprintf('%s/routes/module/%s.php', base_path(), $routes);

                    include $fullRoutePath;
                }
            });
        });
    }
}
