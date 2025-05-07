<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Database\Seeders\TaxRatesSeeder;

use Modules\Core\Models\TaxRate;

use Modules\Core\Models\Company;

use Illuminate\Database\Seeder;

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
