# laravel-decorators

[![Latest Stable Version](https://poser.pugx.org/crixuamg/laravel-decorators/v/stable)](https://packagist.org/packages/crixuamg/laravel-decorators)
[![Latest Unstable Version](https://poser.pugx.org/crixuamg/laravel-decorators/v/unstable)](https://packagist.org/packages/crixuamg/laravel-decorators)
[![Total Downloads](https://poser.pugx.org/crixuamg/laravel-decorators/downloads)](https://packagist.org/packages/crixuamg/laravel-decorators)

[About](#about)<br>
[Installation](#installation)<br>
[Usage](#about)<br>
[Customization](#customization)<br>
[Commands](#commands)<br>

## About
This package is designed to allow developers (inluding myself of course!) to start developing complex applications more easily. By using this design pattern I saved more than a couple of hours on projects, both personal and professional.

## Installation
Put the following in your composer.json file:
```json
    "require": {
        "crixuamg/laravel-decorators": "^2.0.0",
        // ...
    }
```

## Usage
Decorators can be registered in two ways.

1) Config based \
After publishing the config file, register your decorators as shown in the bottom of the file. Then extend the AbstractController in a controller and call `$this->setup()` in the `__construct()` using the key created in the config file.

    Example:\
    UserController

    ```php
    use \CrixuAMG\Decorators\Http\Controllers\AbstractController;

    class UserController extends AbstractController {
        public function __construct()
        {
            $this->setup('users', UserResource::class);
        }

        public function index() {
           return $this->forwardCachedResourceful(__FUNCTION__);
        }
     }
    ```
    And put the following in
    config/decorators.php

    ```php
        'matchables' => [
            'users'                => [
                '__contract'  => App\Contracts\UserContract::class,
                '__arguments' => [
                    // First element is the deepest layer
                    App\Repositories\UserRepository::class,
                    App\Caches\UserCache::class,
                    App\Decorators\UserDecorator::class,
                ],
            ],
        ]
    ```

   When hitting the route linked to the index method the application will go through the UserDecorator, UserCache and UserRepository. Then it will go back through the same classes, passing the returned data and performing the required actions, like caching and firing events.
   After everything has been processed the data will be returned using the resource as stated in the `__construct`.


2) ServiceProvider based\
Run `php artisan make:provider RepositoryServiceProvider`.
Then, within the newly created provider register any set of class that you want to use the decorator pattern with.
Example:
```php
    use CrixuAMG\Decorators\Decorator;

    public function register()
    {
        $decorator = new Decorator($this->app);

        $decorator->decorate(UserContract::class, [
            UserRepository::class,
            UserCache::class,
        ]);
    }
```

## Customization
You can set an `enabled` flag within the `config/decorators.php` file.
When this is set to false, any decorators implementing the `CrixuAMG\Decorators\Caches\AbstractCache` class will be ignored.

## Commands
To make development even faster and easier, some commands have been created to improve ease of use.

```bash
    php artisan decorator:cache      ModelCache
    php artisan decorator:repository ModelRepository
    php artisan decorator:contract   ModelContract
```

Or, create all three in a single command:
```bash
    php artisan decorators:make User
```

For your convenience, a command has been included that automatically creates the following:
- Model
- Controller
- Contract
- Cache
- Repository
- Resource

```bash
    php artisan decorators:starter User
```
The following options can be used to create extra files:
- `-m` Creates a migration
- `-d` Creates a decorator
- `-r` Creates 4 request classes (Show, Store, Update, Delete) in app/Http/Requests/<MODEL_NAME>/
