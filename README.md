# laravel-decorators

## Installation
Put the following in your composer.json file: 
```json
    "require": {
        "crixuamg/laravel-decorators": "dev-develop",
        // ...
    }
```

## Usage
Run `php artisan make:provider RepositoryServiceProvider`.
Then, within the newly created provider register any set of class that you want to use the decorator pattern with.
Example:
```php
    use CrixuAMG\Decorators\Handler;

    public function register() 
    {
        /**
         * Model repository
         */
        $this->app->singleton(ModelContract::class, function () {
            return Handler::handlerFactory(ModelContract::class, [
                ModelRepository::class,
                ModelCache::class,
                // Put any decorators below here
            ]);
        });
    }
```

## Customization
You can set an `enabled` flag within the `config/cache.php` file.
When this is set to false, any decorators implementing the `CrixuAMG\Decorators\Caches\AbstractCache` class will be ignored.
 