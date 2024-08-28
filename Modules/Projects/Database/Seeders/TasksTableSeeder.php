<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Projects\Models\Task;

class TasksTableSeeder extends Seeder
{
    public function run(): void
    {
        Task::factory()->count(500)->create();
    }
}
