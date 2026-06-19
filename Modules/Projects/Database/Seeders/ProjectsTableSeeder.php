<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Projects\Models\Project;

class ProjectsTableSeeder extends Seeder
{
    public function run(): void
    {
        Project::factory()->count(500)->create();
    }
}
