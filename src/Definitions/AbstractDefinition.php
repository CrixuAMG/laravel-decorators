<?php

namespace CrixuAMG\Decorators\Definitions;

use CrixuAMG\Decorators\Contracts\DefinitionContract;
use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractDefinition implements DefinitionContract, Arrayable
{
    protected $definitionKey = 'definition';
    protected $sortableKey = 'sortable';
    protected $filterableKey = 'filterable';
    protected $relationsKey = 'relations';

    public function toArray()
    {
        return $this->definition();
    }

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
        $requestedRelations = explode('|', request()->relations ?? '');
        $validatedRelations = [];

        foreach ($requestedRelations as $requestedRelation) {
            $requestedRelationParts = explode(':', trim($requestedRelation));
            $relation = reset($requestedRelationParts);

            if (in_array($relation, $this->queryableRelations())) {
                $validatedRelations[] = $requestedRelation;
            }
        }

        return $validatedRelations;
    }
}
