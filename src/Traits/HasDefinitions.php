<?php

namespace CrixuAMG\Decorators\Traits;

use CrixuAMG\Decorators\Contracts\DefinitionContract;

/**
 * Trait HasDefinitions
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait HasDefinitions
{
    /**
     * @var string
     */
    protected $definition = null;

    /**
     * @return array
     */
    public function getDefinition(): array
    {
        return $this->getDefinitionInstance()->definition();
    }

    /**
     * @param null $definition
     *
     * @return $this
     */
    public function setDefinition($definition = null)
    {
        if (!$definition) {
            return $this;
        }

        $this->definition = $definition;

        if (isset($this->next)) {
            $this->next->setDefinition($definition);
        }

        return $this;
    }

    /**
     * @return DefinitionContract
     */
    protected function getDefinitionInstance(): DefinitionContract
    {
        return new $this->definition();
    }

    /**
     * @return array
     */
    public function sortableColumns(): array
    {
        return $this->getDefinitionInstance()->sortableColumns();
    }

    /**
     * @return array
     */
    public function filterableColumns(): array
    {
        return $this->getDefinitionInstance()->filterableColumns();
    }

    /**
     * @return array
     */
    public function queryableRelations(): array
    {
        return $this->getDefinitionInstance()->queryableRelations();
    }
}
