<?php

namespace CrixuANG\Decorators\Modules;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * Class EventModule
 *
 * @package CrixuANG\Decorators\Traits
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
    private $updatableField;
    /**
     * @var
     */
    private $target;

    /**
     * @param       $class
     * @param mixed ...$args
     *
     * @throws \Throwable
     *
     * @return EventModule
     */
    public function fireEvent($class, ...$args): EventModule
    {
        if (!\is_string($class)) {
            $class = \get_class($class);
        }

        throw_unless(
            $class,
            \UnexpectedValueException::class,
            'The specified class is not an object',
            422
        );

        throw_unless(
            isset(class_uses($class)[Queueable::class]),
            \UnexpectedValueException::class,
            'The specified class does not implement Queueable',
            422
        );

        $updatableField = $this->getUpdateAbleField();
        $updatableModel = $this->getAutoUpdateModel();
        $target         = $this->getTarget();

        // Update the field if both variables are filled
        if ($updatableField && $updatableModel) {
            if (!$updatableModel->{$updatableField}) {
                $updatableModel->update([
                    // Todo, allow the type to be set
                    $updatableField => true,
                ]);
                if ($target) {
                    $target->notify(new $class(...$args));
                } else {
                    Notification::notify(new $class(...$args));
                }
            }
        } else {
            if ($target) {
                Notification::send($target, new $class(...$args));
            } else {
                Notification::notify(new $class(...$args));
            }
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
    public function fireEventIf($class, bool $statement, ...$args)
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
    public function fireEventUnless($class, bool $statement, ...$args)
    {
        if (!$statement) {
            return $this->fireEvent($class, ...$args);
        }

        return false;
    }

    /**
     * @param string $updatableField
     *
     * @return EventModule
     */
    public function setUpdatableField(string $updatableField): EventModule
    {
        $this->updatableField = $updatableField;

        return $this;
    }

    /**
     * @param Model $autoUpdateModel
     *
     * @return EventModule
     */
    public function setAutoUpdateModel(Model $autoUpdateModel): EventModule
    {
        $this->autoUpdateModel = $autoUpdateModel;

        return $this;
    }

    /**
     * The target to send the notification to
     *
     * @param mixed $target
     *
     * @return EventModule
     */
    public function setTarget($target): EventModule
    {
        if ($target instanceof Collection) {
            $target->each(function ($targetModel) {
                if ($targetModel instanceof Model) {
                    $this->validateNotifiableTarget($targetModel);
                } else {
                    $this->invalidTarget();
                }
            });
        } elseif ($target instanceof Model) {
            $this->validateNotifiableTarget($target);
        } else {
            $this->invalidTarget();
        }

        $this->target = $target;

        return $this;
    }

    /**
     * @return bool
     */
    private function getUpdateAbleField()
    {
        return $this->updatableField;
    }

    /**
     * @return mixed
     */
    private function getAutoUpdateModel()
    {
        return $this->autoUpdateModel;
    }

    /**
     * @return mixed
     */
    private function getTarget()
    {
        return $this->target;
    }

    /**
     * @param Model $target
     */
    private function validateNotifiableTarget(Model $target)
    {
        if (!isset(class_uses($target)['Illuminate\Notifications\Notifiable'])) {
            $this->invalidTarget();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function invalidTarget()
    {
        throw new InvalidArgumentException('The set target is not valid.');
    }
}