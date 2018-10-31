<?php

namespace CrixuAMG\Decorators\Test\Providers;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TestModel
 * @package CrixuAMG\Decorators\Test\Providers
 */
class TestModel extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'foo',
    ];
}
