# laravel-decorators

[![Build Status](https://travis-ci.org/CrixuAMG/laravel-decorators.svg?branch=master)](https://travis-ci.org/CrixuAMG/laravel-decorators)

[About](#about)<br>
[Installation](#installation)<br>
[Usage](#about)<br>
[Customization](#customization)<br>
[Commands](#commands)<br>

## About
This package is designed to allow developers (inluding myself of course!) to start developing complex applications more easily. By using this design pattern I saved more than a couple of hours on projects, both personal and professional.

But what is the decorator pattern?<br>
Wikipedia: "In object-oriented programming, the decorator pattern is a design pattern that allows behavior to be added to an individual object, either statically or dynamically, without affecting the behavior of other objects from the same class"

[Wikipedia link](https://en.wikipedia.org/wiki/Decorator_pattern)

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
        // Create an instance of the Handler class
        $decorator = app(Handler::class);

        /**
         * Model repository
         */
        $decorator->decorate(ModelContract::class, [
            ModelRepository::class,
            ModelCache::class,
        ]);
    }
```

## Customization
You can set an `enabled` flag within the `config/cache.php` file.
When this is set to false, any decorators implementing the `CrixuAMG\Decorators\Caches\AbstractCache` class will be ignored.
 
## Commands
To make development even faster and easier, some commands have been created to improve ease of use.

```bash
    php artisan make:trait      ModelTrait
    php artisan make:cache      ModelCache
    php artisan make:repository ModelRepository
    php artisan make:contract   ModelContract
```