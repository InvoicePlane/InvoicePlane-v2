<?php

namespace Modules\Projects\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class TasksSeeder extends AbstractSeeder
{
    protected string $label        = 'Tasks';
    protected int    $defaultCount = 15;

    protected function buildOne(): void
    {
        $project = Project::query()
            ->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->firstOrFail();

        Task::factory()
            ->state(['company_id' => $this->companyId, 'project_id' => $project->id])
            ->create();
    }
}
