<?php

namespace CrixuAMG\Decorators\Definitions;

use CrixuAMG\Decorators\Contracts\DefinitionContract;

abstract class AbstractDefinition implements DefinitionContract
{
    public $definitionKey = 'definition';
    public $sortableKey = 'sortable';
    public $filterableKey = 'filterable';
    public $relationsKey = 'relations';

    public function definition(): array
    {
        return [
            $this->definitionKey => [
                $this->sortableKey   => $this->sortableColumns(),
                $this->filterableKey => $this->filterableColumns(),
                $this->relationsKey  => $this->queryableRelations(),
            ],
        ];
    }
}
