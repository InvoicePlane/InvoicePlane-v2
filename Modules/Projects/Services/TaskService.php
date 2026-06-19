<?php

namespace Modules\Projects\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Projects\Events\TaskWasCreated;
use Modules\Projects\Events\TaskWasUpdated;
use Modules\Projects\Models\Task;

class TaskService extends BaseService
{
    public function model(): string
    {
        return Task::class;
    }

    public function create(array $validatedInput): Task
    {
        $task = new Task(
            $validatedInput
        );

        $task->save();

        event(new TaskWasCreated());

        return $task;
    }

    public function update(array $validatedInput, $taskToUpdate): Model
    {
        $taskToUpdate->fill($validatedInput);

        $taskToUpdate->save();

        event(new TaskWasUpdated());

        return $taskToUpdate;
    }
}
