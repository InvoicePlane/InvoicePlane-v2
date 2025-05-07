<?php

namespace Modules\Payments\Database\Seeders;

use Modules\Payments\Models\PaymentMethod;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Database\Seeders\PaymentMethodsSeeder;

use Modules\Core\Models\Company;

use Illuminate\Database\Seeder;

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
