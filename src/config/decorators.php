<?php

use CrixuAMG\Decorators\Modules\EventModule;
use CrixuAMG\Decorators\Modules\SecurityModule;

return [
    /**
     * When this is disabled, any classes implementing
     * the CrixuAMG\Decorators\Caches\AbstractCache will be ignored
     */
    'enabled'           => true,

    /**
     * The amount of minutes a set of cached data is valid
     * After that the cache will be refreshed with new data
     */
    'cache_minutes'     => 60,

    /**
     * Define modules here as a Classname => Namespace combination
     * Within any class implementing the AbstractDecorator class, the $this->(Namespace) value will be set
     */
    'modules'           => [
        EventModule::class => 'event',
    ],

    /**
     * When set to true, the modules in the array above will be available
     * In any class implementing the AbstractDecorator class
     */
    'modules_enabled'   => true
];
