<?php

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
];
