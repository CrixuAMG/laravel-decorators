<?php

namespace CrixuAMG\Decorators\Definitions;

use CrixuAMG\Decorators\Contracts\DefinitionContract;

abstract class AbstractDefinition implements DefinitionContract
{
    protected $definitionKey = 'definition';
    protected $sortableKey = 'sortable';
    protected $filterableKey = 'filterable';
    protected $relationsKey = 'relations';

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

    public function requestedRelations(): array
    {
        $requestedRelations = explode(', ', request()->relations ?? '');
        $validatedRelations = [];

        foreach ($requestedRelations as $requestedRelation) {
            if (in_array($requestedRelation, $this->queryableRelations())) {
                $validatedRelations[] = $requestedRelation;
            }
        }

        return $validatedRelations;
    }
}
