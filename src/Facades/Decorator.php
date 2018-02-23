<?php

namespace CrixuAMG\Decorators\Facades;

use CrixuAMG\Decorators\Handler;
use Illuminate\Support\Facades\Facade;

class Decorator extends Facade
{
    /**
     * @return mixed
     */
    protected static function getFacadeAccessor()
    {
        return Handler::class;
    }
}