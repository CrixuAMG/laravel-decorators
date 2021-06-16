<?php

namespace CrixuAMG\Decorators\Definitions;

use CrixuAMG\Decorators\Models\BaseModel;

class TestModel extends BaseModel
{
    public static function definition()
    {
        return (new TestDefinition())->definition();
    }
}
