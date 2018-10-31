<?php

return [
    'cache'            => [
        /**
         * When this is disabled, any classes implementing
         * the CrixuAMG\Decorators\Caches\AbstractCache will be ignored
         */
        'enabled'            => env('APP_CACHE_ENABLED', true),
        /**
         * The amount of minutes a set of cached data is valid
         * After that the cache will be refreshed with new data
         */
        'minutes'            => (int)env('APP_CACHE_MINUTES', 60),
        /**
         * The parameters that can be retrieved from the request for the cache key
         */
        'request_parameters' => [
            'page',
        ],
        /**
         * Enable this to enforce tags throughout cache classes and controllers extending the AbstractController
         * This is only used for cache drivers that support the tags feature
         *
         * https://laravel.com/docs/master/cache#cache-tags
         */
        'enable_forced_tags' => (bool)env('APP_CACHE_FORCE_TAGS', false),
        /**
         * Put any tags that should be used by default
         */
        'default_tags'       => [
            //
        ],
    ],

    /**
     * Place any routes that should get ignored in the array below
     */
    'ignored_routes'   => [
        '',
        '/',
        'oauth/token',
    ],

    /**
     * The maximum amount of items that will be returned by a query that is set to paginate the results
     */
    'pagination'       => (int)env('APP_PAGINATION', 25),

    /**
     * Below is an example of how decorators can automatically be matched using the route
     */
    'route_matchables' => [
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
