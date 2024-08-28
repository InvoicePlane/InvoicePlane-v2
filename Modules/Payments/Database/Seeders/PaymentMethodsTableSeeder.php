<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodsTableSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::factory()->count(5)->create();
    }
}
