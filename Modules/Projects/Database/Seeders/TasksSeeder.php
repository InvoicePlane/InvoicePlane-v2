<?php

namespace Modules\Projects\Database\Seeders;

use Modules\Projects\Models\Task;

use Modules\Projects\Database\Seeders\TasksSeeder;

use Modules\Core\Models\Company;

use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            Task::factory()->count(random_int(15, 25))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
