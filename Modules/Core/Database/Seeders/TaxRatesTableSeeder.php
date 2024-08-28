<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\TaxRate;

class TaxRatesTableSeeder extends Seeder
{
    public function run(): void
    {
        TaxRate::factory()->count(5)->create();
    }
}
