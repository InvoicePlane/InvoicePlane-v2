<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;

class TaxRatesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            TaxRate::factory()->count(random_int(2, 5))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
