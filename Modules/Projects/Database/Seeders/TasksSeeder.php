<?php

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Projects\Models\Task;

class TasksSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            Task::factory()->count(random_int(5, 10))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
