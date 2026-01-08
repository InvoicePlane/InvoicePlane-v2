<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Products\Models\ProductCategory;

class ProductCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            ProductCategory::factory()->count(random_int(1, 2))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
