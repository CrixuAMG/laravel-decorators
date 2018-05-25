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

    'cache' => [
        'request_parameters' => [
            'page',
        ],
    ],

    /**
     * The maximum amount of items that will be returned by a query that is set to paginate the results
     */
    'pagination'    => (int)env('APP_PAGINATION', 25),

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
