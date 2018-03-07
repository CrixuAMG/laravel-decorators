<?php

namespace Salesman\Decorators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Salesman\Contracts\AdvertisementContract;
use Salesman\Mail\AdvertisementTaskAssignedMail;
use Salesman\Models\AdvertisementTaskAssignee;

/**
 * Class AdvertisementDecorator
 *
 * @package Salesman\Decorators
 */
class AdvertisementDecorator extends AbstractDecorator implements AdvertisementContract
{
    /**
     * @param $page
     *
     * @return mixed
     */
    public function taskIndex($page)
    {
        return $this->next->taskIndex($page);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function taskShow(Model $model)
    {
        return $this->next->taskShow($model);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function taskStore(array $data)
    {
        $task = $this->next->taskStore($data);

        if ($task->assignees) {
            $task->loadMissing('assignees.user');

            foreach ($task->assignees as $assignee) {
                $target = $assignee->user->email;

                Mail::to($target)
                    ->send(new AdvertisementTaskAssignedMail(request()->user(), $task));
            }
        }

        return $task;
    }

    /**
     * @param $page
     *
     * @return mixed
     */
    public function taskAssigneeIndex($page)
    {
        return $this->next->taskAssigneeIndex($page);
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function taskAssigneeShow(Model $model)
    {
        return $this->next->taskAssigneeShow($model);
    }

    /**
     * @param AdvertisementTaskAssignee $advertisementTaskAssignee
     *
     * @return mixed
     */
    public function toggleAssigneeCompleted(AdvertisementTaskAssignee $advertisementTaskAssignee)
    {
        return $this->next->toggleAssigneeCompleted($advertisementTaskAssignee);
    }
}