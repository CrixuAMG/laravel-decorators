<?php

namespace CrixuAMG\Decorators\Test\Providers;

use CrixuAMG\Decorators\Traits\HasDefinitions;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TestModel
 * @package CrixuAMG\Decorators\Test\Providers
 */
class TestModel extends Model
{
    use HasDefinitions;

    /**
     * @var array
     */
    protected $fillable = [
        'foo',
    ];
}
