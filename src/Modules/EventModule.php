<?php

namespace CrixuAMG\Decorators\Modules;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait EventModule
 *
 * @package CrixuAMG\Decorators\Traits
 */
trait EventModule
{
    private $autoUpdateModel;
    private $updateAbleField;

    /**
     * @param       $class
     * @param mixed ...$args
     *
     * @throws \Throwable
     */
    public function fireEvent($class, ...$args)
    {
        $class = \get_class($class);

        throw_unless(
            $class,
            \UnexpectedValueException::class,
            'The specified class is not an object',
            422
        );

        // TODO check it uses Dispatchable (class_uses)

        if ($this->getUpdateAbleField() && $this->getAutoUpdateModel()) {
            $this->getAutoUpdateModel()->update([
                // Todo, allow the type to be set
                $this->getUpdateAbleField() => true,
            ]);
        }

        // Todo: check whether it should be fired or not

        $class::dispatch(...$args);

        return $this;
    }

    /**
     * @return bool
     */
    public function getUpdateAbleField()
    {
        return $this->updateAbleField;
    }

    /**
     * @param string $updateAbleField
     *
     * @return EventModule
     */
    public function setUpdateAbleField(string $updateAbleField)
    {
        $this->updateAbleField = $updateAbleField;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAutoUpdateModel()
    {
        return $this->autoUpdateModel;
    }

    /**
     * @param Model $autoUpdateModel
     *
     * @return EventModule
     */
    public function setAutoUpdateModel(Model $autoUpdateModel)
    {
        $this->autoUpdateModel = $autoUpdateModel;

        return $this;
    }
}
