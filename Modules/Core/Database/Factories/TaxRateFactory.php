<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use RuntimeException;

/**
 * @extends Factory<TaxRate>
 */
class TaxRateFactory extends AbstractFactory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        $rates = [
            ['name' => 'VAT Standard', 'rate' => 21.00],
            ['name' => 'VAT Reduced', 'rate' => 9.00],
            ['name' => 'VAT Super Reduced', 'rate' => 6.00],
            ['name' => 'Sales Tax US', 'rate' => 7.50],
            ['name' => 'Zero Tax', 'rate' => 0.00],
            ['name' => 'Belgium VAT', 'rate' => 21.00],
            ['name' => 'Germany VAT', 'rate' => 19.00],
            ['name' => 'UK VAT', 'rate' => 20.00],
            ['name' => 'Netherlands VAT', 'rate' => 21.00],
            ['name' => 'California Sales Tax', 'rate' => 8.25],
        ];

        $selected = $this->faker->randomElement($rates);
        $company  = $this->company ?? Company::query()->inRandomOrder()->first();

        if ( ! $company) {
            throw new RuntimeException('No company available for TaxRate factory');
        }

        return [
            'company_id'    => $company->id,
            'tax_rate_type' => $this->faker->randomElement(TaxRateType::cases())->value,
            'is_active'     => $this->faker->boolean(90),
            'code'          => mb_strtoupper($this->faker->unique()->bothify('TAX#####')),
            'name'          => $selected['name'],
            'rate'          => $selected['rate'],
        ];
    }
}
