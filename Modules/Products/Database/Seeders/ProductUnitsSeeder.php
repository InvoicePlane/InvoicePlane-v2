<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Products\Models\ProductUnit;

class ProductUnitsSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            ProductUnit::factory()->count(random_int(2, 5))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
