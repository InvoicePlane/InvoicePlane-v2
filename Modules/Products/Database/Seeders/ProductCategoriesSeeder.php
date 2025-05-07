<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;

class ProductCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            ProductCategory::factory()->count(random_int(5, 15))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
