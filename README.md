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
        "crixuamg/laravel-decorators": "^1.0.0",
        // ...
    }
```

## Usage
Decorators can be registered in two ways.

1) Config based \
After publishing the config file, register your decorators as shown in the bottom of the file. Then extend the AbstractController in a controller and call `$this-setup()` in the `__construct()` using the key created in the config file.

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
The classes with automatically get their correct name based on the name provided.