<?php

use CrixuAMG\Decorators\Modules\EventModule;
use CrixuAMG\Decorators\Modules\SecurityModule;

return [
    /**
     * When this is disabled, any classes implementing
     * the CrixuAMG\Decorators\Caches\AbstractCache will be ignored
     */
    'enabled'       => true,

    /**
     * The amount of minutes a set of cached data is valid
     * After that the cache will be refreshed with new data
     */
    'cache_minutes' => 60,

    /**
     * Define modules here as a Classname => Namespace combination
     * Within any class implementing the AbstractDecorator, the $this->(Namespace) value will be set
     */
    'modules'       => [
        EventModule::class => 'event',
    ],
];
