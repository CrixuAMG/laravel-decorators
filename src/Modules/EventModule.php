<?php

namespace CrixuAMG\Decorators\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Notifications\Notification;

/**
 * Class EventModule
 *
 * @package CrixuAMG\Decorators\Traits
 */
class EventModule
{
    /**
     * @var
     */
    private $autoUpdateModel;
    /**
     * @var
     */
    private $updateAbleField;
    /**
     * @var
     */
    private $target;

    /**
     * @param       $class
     * @param mixed ...$args
     *
     * @throws \Throwable
     */
    protected function fireEvent($class, ...$args)
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
        $target = $this->getTarget();

        // Update the field if both variables are filled
        if ($updatableField && $updatableModel) {
            if (!$updatableModel->{$updatableField}) {
                $updatableModel->update([
                    // Todo, allow the type to be set
                    $updatableField => true,
                ]);

                if ($target) {
                    $target->notify(new $class, ...$args);
                } else {
                    Notification::notify(new $class(...$args));
                }
            }
        } else {
            Notification::notify(new $class(...$args));
        }

        return $this;
    }

    /**
     * @param       $class
     * @param bool  $statement
     * @param mixed ...$args
     *
     * @return bool|EventModule
     * @throws \Throwable
     */
    protected function fireEventIf($class, bool $statement, ...$args)
    {
        if ($statement) {
            return $this->fireEvent($class, ...$args);
        }

        return false;
    }

    /**
     * @param       $class
     * @param bool  $statement
     * @param mixed ...$args
     *
     * @return bool|EventModule
     * @throws \Throwable
     */
    protected function fireEventUnless($class, bool $statement, ...$args)
    {
        if (!$statement) {
            return $this->fireEvent($class, ...$args);
        }

        return false;
    }

    /**
     * @return bool
     */
    private function getUpdateAbleField()
    {
        return $this->updateAbleField;
    }

    /**
     * @param string $updateAbleField
     *
     * @return EventModule
     */
    protected function setUpdateAbleField(string $updateAbleField)
    {
        $this->updateAbleField = $updateAbleField;

        return $this;
    }

    /**
     * @return mixed
     */
    private function getAutoUpdateModel()
    {
        return $this->autoUpdateModel;
    }

    /**
     * @param Model $autoUpdateModel
     *
     * @return EventModule
     */
    protected function setAutoUpdateModel(Model $autoUpdateModel)
    {
        $this->autoUpdateModel = $autoUpdateModel;

        return $this;
    }

    /**
     * @return mixed
     */
    private function getTarget()
    {
        return $this->target;
    }

    /**
     * The target to send the notification to
     *
     * @param mixed $target
     */
    protected function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }
}
