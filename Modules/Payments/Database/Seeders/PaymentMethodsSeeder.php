<?php

namespace Modules\Payments\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodsSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            PaymentMethod::factory()->count(random_int(2, 4))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
