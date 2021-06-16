<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Definitions\BaseDefinition;

trait Definable
{
    protected $definition = BaseDefinition::class;

    public function definition(): array
    {
        return (new $this->definition())->definition();
    }
}
