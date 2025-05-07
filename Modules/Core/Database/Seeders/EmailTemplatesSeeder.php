<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Database\Seeders\EmailTemplatesSeeder;

use Modules\Core\Models\EmailTemplate;

use Modules\Core\Models\Company;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\EmailTemplate;

class EmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            EmailTemplate::factory()->count(random_int(2, 5))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
