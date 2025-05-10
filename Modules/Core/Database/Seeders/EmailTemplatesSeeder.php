<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;

class EmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            EmailTemplate::factory()->count(random_int(2, 3))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
