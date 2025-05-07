<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Database\Seeders\DocumentGroupsSeeder;

use Modules\Core\Models\Company;

use Modules\Core\Models\DocumentGroup;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;

class DocumentGroupsSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            DocumentGroup::factory(random_int(2, 5))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
