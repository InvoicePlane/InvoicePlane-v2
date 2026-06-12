<?php

namespace Modules\Projects\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Projects\Models\Task;
use Throwable;

class TaskService extends BaseService
{
    public function model(): string
    {
        return Task::class;
    }

    public function createTask(array $data): Model
    {
        DB::beginTransaction();

        $customer_id = app(ProjectService::class)->getCustomer($data['project_id']);

        try {
            $task = Task::query()->create([
                'task_name'   => $data['task_name'],
                'task_status' => $data['task_status'] ?? 'not_started',
                'project_id'  => $data['project_id'] ?? null,
                'customer_id' => $customer_id,
                'tax_rate_id' => $data['tax_rate_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'task_price'  => $data['task_price'] ?? null,
                'due_at'      => $data['due_at'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            DB::commit();

            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTask(Task $model, array $data): Task
    {
        DB::beginTransaction();

        try {
            $updateData = [
                'task_name'   => $data['task_name'] ?? $model->task_name,
                'task_status' => $data['task_status'] ?? $model->task_status,
                'project_id'  => $data['project_id'] ?? $model->project_id,
                'customer_id' => $data['customer_id'] ?? $model->customer_id,
                'tax_rate_id' => $data['tax_rate_id'] ?? $model->tax_rate_id,
                'assigned_to' => $data['assigned_to'] ?? $model->assigned_to,
                'task_price'  => $data['task_price'] ?? $model->task_price,
                'due_at'      => $data['due_at'] ?? $model->due_at,
                'description' => $data['description'] ?? $model->description,
            ];

            $model->update($updateData);
            DB::commit();

            return $model->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteTask(Task $task): Task
    {
        DB::beginTransaction();
        try {
            $task->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $task;
    }
}
