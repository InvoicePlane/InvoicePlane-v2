<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payments\Models\Payment;

class PaymentsTableSeeder extends Seeder
{
    public function run(): void
    {
        Payment::factory()->count(50)->create();
    }
}
