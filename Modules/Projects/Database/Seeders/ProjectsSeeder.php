<?php

namespace Modules\Projects\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Projects\Models\Project;

class ProjectsSeeder extends AbstractSeeder
{
    protected string $label = 'Projects';

    protected int    $defaultCount = 5;

    protected function buildOne(): void
    {
        Project::factory()
            ->state(['company_id' => $this->companyId])
            ->create();
    }
}
