<?php

namespace Modules\Projects\Database\Seeders;

use Modules\Clients\Enums\RelationType;
use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Task;

class TasksSeeder extends AbstractSeeder
{
    protected string $label = 'Tasks';

    protected int $defaultCount = 25;

    protected function buildOne(): void
    {
        $customer = $this->findOrCreateRelationOfType($this->companyId, RelationType::CUSTOMER);
        $project = $this->findOrCreateProject($this->companyId);
        $user = $this->findOrCreateUser($this->companyId);

        Task::factory()
            ->state([
                'company_id'  => $this->companyId,
                'customer_id' => $customer->id,
                'project_id'  => $project->id,
                'assigned_to' => $user->id,
                'task_status' => fake()->randomElement(TaskStatus::cases())->value,
            ])
            ->create();
    }
}
