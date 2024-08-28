<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Products\Models\ProductFamily;

class FamiliesTableSeeder extends Seeder
{
    public function run(): void
    {
        ProductFamily::factory()->count(5)->create();
    }
}
