<?php

namespace Modules\Products\Database\Seeders;

use Modules\Products\Database\Seeders\ProductsSeeder;

use Modules\Products\Models\Product;

use Modules\Core\Models\Company;

use Illuminate\Database\Seeder;


class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            Product::factory()->count(50)->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
