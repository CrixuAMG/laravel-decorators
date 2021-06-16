<?php

namespace CrixuAMG\Decorators\Models;

use CrixuAMG\Decorators\Traits\Definable;
use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\Resultable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 *
 * @package CrixuAMG\Decorators\Models
 */
class BaseModel extends Model
{
    use Resultable, Definable, HasCaching;
}
