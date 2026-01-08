<?php

namespace Modules\Projects\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Projects\Enums\ProjectStatus;
use Modules\Projects\Models\Project;

class ProjectsSeeder extends AbstractSeeder
{
    protected string $label = 'Projects';

    protected int $defaultCount = 25;

    protected function buildOne(): void
    {
        $customer = $this->findOrCreateCustomer($this->companyId);

        Project::factory()
            ->state([
                'company_id'     => $this->companyId,
                'customer_id'    => $customer->id,
                'project_status' => fake()->randomElement(ProjectStatus::cases())->value,
            ])
            ->create();
    }
}
