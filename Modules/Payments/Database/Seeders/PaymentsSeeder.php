<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Payments\Models\Payment;

class PaymentsSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            Payment::factory()->count(random_int(5, 15))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
