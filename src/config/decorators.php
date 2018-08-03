<?php

return [
    'cache'          => [
        /**
         * When this is disabled, any classes implementing
         * the CrixuAMG\Decorators\Caches\AbstractCache will be ignored
         */
        'enabled'       => env('APP_CACHE_ENABLED', true),
        /**
         * The amount of minutes a set of cached data is valid
         * After that the cache will be refreshed with new data
         */
        'minutes' => 60,
        /**
         * The parameters that can be retrieved from the request for the cache key
         */
        'request_parameters' => [
            'page',
        ],
    ],

    /**
     * Place any routes that should get ignored in the array below
     */
    'ignored_routes' => [
        '/',
    ],

    /**
     * The maximum amount of items that will be returned by a query that is set to paginate the results
     */
    'pagination'     => (int)env('APP_PAGINATION', 25),

    /**
     * The provider that will be used for matching decorators when using the autoregisterRoute method
     *
     * The following options are available: config (string) and a callback, that should return an array
     * When config is used, it uses the array below this element
     */
    'route_matchable_provider' => 'config',

    'route_matchables' => [
        /**
         * Below is an example of how decorators can automatically be matched using the route
         */
        // 'v1' => [
        //     'users'   => [
        //         '__contract'  => UserContract::class,
        //         '__arguments' => [
        //             UserRepository::class,
        //             UserCache::class
        //         ],
        //     ],
        // ]
    ],
];
