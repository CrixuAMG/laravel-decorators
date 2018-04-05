<?php

namespace CrixuAMG\Decorators\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class EventModule
 *
 * @package CrixuAMG\Decorators\Traits
 */
class EventModule
{
    private $autoUpdateModel;
    private $updateAbleField;
    private $target;

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

        throw_unless(
            isset(class_uses($class)[Dispatchable::class]),
            \UnexpectedValueException::class,
            'The specified class does not implement Dispatchable',
            422
        );

        $updatableField = $this->getUpdateAbleField();
        $updatableModel = $this->getAutoUpdateModel();

        // Update the field if both variables are filled
        if ($updatableField && $updatableModel) {
            $updatableModel->update([
                // Todo, allow the type to be set
                $updatableField => true,
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

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * The target to send the notification to
     *
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }
}
