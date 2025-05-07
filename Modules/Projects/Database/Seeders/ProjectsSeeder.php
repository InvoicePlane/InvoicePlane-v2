<?php

namespace Modules\Projects\Database\Seeders;

use Modules\Projects\Models\Project;

use Modules\Core\Models\Company;

use Modules\Projects\Database\Seeders\ProjectsSeeder;

use Illuminate\Database\Seeder;

class ProjectsSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            Project::factory()->count(random_int(15, 25))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
