<?php

namespace CrixuAMG\Decorators\Contracts;

interface DefinitionContract
{
    public function sortableColumns(): array;

    public function filterableColumns(): array;

    public function scopes(): array;

    public function queryableRelations(): array;
}
