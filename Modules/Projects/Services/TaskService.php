<?php

namespace Modules\Projects\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Task;

class TaskService extends BaseService
{
    public function model(): string
    {
        return Task::class;
    }

    public function createTask(array $data): Model
    {
        return $this->create([
            'title'  => $data['title'],
            'status' => $data['status'],
        ]);
    }

    public function updateTask(Task $model, array $data): Task
    {
        $model->update([
            'title'  => $data['title'],
            'status' => $data['status'],
        ]);

        return $model;
    }
}
