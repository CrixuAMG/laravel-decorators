<?php

namespace CrixuAMG\Decorators\Services\Module;

class Module
{
    public static function boot()
    {
        ModuleRouteMacroRegistrar::register();
    }
}