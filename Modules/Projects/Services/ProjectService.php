<?php

namespace Modules\Projects\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

class ProjectService extends BaseService
{
    public function model(): string
    {
        return Project::class;
        // event(new ProjectWasCreated());
        // event(new ProjectWasUpdated());
    }

    public function createProject(array $data): Model
    {
        return $this->create([
            'customer_id'    => $data['customer_id'],
            'project_status' => $data['project_status'] ?? ProjectStatus::PLANNED->value,
            'project_name'   => $data['project_name'],
            'description'    => $data['description'] ?? null,
            'start_at'       => $data['start_at'] ?? now(),
            'end_at'         => $data['end_at'] ?? null,
        ]);
    }

    public function updateProject(Project $model, array $data): Project
    {
        $model->update([
            'customer_id'    => $data['customer_id'],
            'project_status' => $data['project_status'] ?? ProjectStatus::PLANNED->value,
            'project_name'   => $data['project_name'],
            'description'    => $data['description'] ?? null,
            'start_at'       => $data['start_at'] ?? now(),
            'end_at'         => $data['end_at'] ?? null,
        ]);

        return $model;
    }

    public function getCustomer(int $project_id): int
    {
        return Project::query()->where('id', $project_id)->value('customer_id');
    }
}
