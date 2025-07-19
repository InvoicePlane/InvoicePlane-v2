<?php

namespace Modules\Projects\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
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
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'starts_at'   => $data['starts_at'],
            'ends_at'     => $data['ends_at'] ?? null,
        ]);
    }

    public function updateProject(Project $model, array $data): Project
    {
        $model->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'starts_at'   => $data['starts_at'],
            'ends_at'     => $data['ends_at'] ?? null,
        ]);

        return $model;
    }

    public function getCustomer(int $project_id): int
    {
        return Project::query()->where('id', $project_id)->value('customer_id');
    }
}
