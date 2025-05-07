<?php

namespace Modules\Products\Database\Seeders;

use Modules\Products\Database\Seeders\ProductCategoriesSeeder;

use Modules\Products\Models\ProductCategory;

use Modules\Core\Models\Company;

use Illuminate\Database\Seeder;

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
