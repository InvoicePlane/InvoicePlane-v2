<?php

namespace Modules\Projects\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;
use Throwable;

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
        DB::beginTransaction();
        try {
            $project = Project::query()->create([
                'customer_id'    => $data['customer_id'],
                'project_status' => $data['project_status'] ?? ProjectStatus::PLANNED->value,
                'project_name'   => $data['project_name'],
                'description'    => $data['description'] ?? null,
                'start_at'       => $data['start_at'] ?? now(),
                'end_at'         => $data['end_at'] ?? null,
            ]);

            DB::commit();

            return $project;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update([
            'customer_id'    => $data['customer_id'],
            'project_status' => $data['project_status'] ?? ProjectStatus::PLANNED->value,
            'project_name'   => $data['project_name'],
            'description'    => $data['description'] ?? null,
            'start_at'       => $data['start_at'] ?? now(),
            'end_at'         => $data['end_at'] ?? null,
        ]);

        return $project;
    }

    public function getCustomer(int $project_id): int
    {
        return Project::query()->where('id', $project_id)->value('customer_id');
    }

    public function deleteProject(Project $project): Project
    {
        DB::beginTransaction();
        try {
            $project->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $project;
    }
}
