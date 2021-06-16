<?php

namespace CrixuAMG\Decorators\Definitions;

use CrixuAMG\Decorators\Contracts\DefinitionContract;

abstract class AbstractDefinition implements DefinitionContract
{
    public function definition(): array
    {
        return [
            'sortable'   => $this->sortableColumns(),
            'filterable' => $this->filterableColumns(),
            'relations'  => $this->queryableRelations(),
        ];
    }
}
