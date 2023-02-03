<?php

namespace CrixuAMG\Decorators\Models;

use Illuminate\Database\Eloquent\Model;
use CrixuAMG\Decorators\Traits\Resultable;
use CrixuAMG\Decorators\Traits\HasCaching;
use CrixuAMG\Decorators\Traits\HasDefinitions;

/**
 * Class BaseModel
 *
 * @package CrixuAMG\Decorators\Models
 */
class BaseModel extends Model
{
    use Resultable, HasCaching, HasDefinitions;
}
