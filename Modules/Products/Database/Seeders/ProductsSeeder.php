<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;


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
