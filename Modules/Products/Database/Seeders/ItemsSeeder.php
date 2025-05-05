<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Products\Models\Item;

class ItemsSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            Item::factory()->count(50)->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
