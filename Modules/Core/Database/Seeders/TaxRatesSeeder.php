<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Enums\TaxRateType;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;

class TaxRatesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    protected array $europeanVatRates = [
        ['name' => 'EU Standard VAT (20%)', 'code' => 'EU-VAT-STD-20', 'rate' => 20.00],
        ['name' => 'EU Reduced VAT (10%)', 'code' => 'EU-VAT-RED-10', 'rate' => 10.00],
        ['name' => 'EU Super Reduced VAT (5%)', 'code' => 'EU-VAT-SUP-5', 'rate' => 5.00],
        ['name' => 'EU Zero Rate (0%)', 'code' => 'EU-VAT-ZERO', 'rate' => 0.00],
    ];

    protected array $usSalesTaxRates = [
        ['name' => 'US Standard Sales Tax (7.25%)', 'code' => 'US-SALES-STD', 'rate' => 7.25],
        ['name' => 'US Reduced Sales Tax (4%)', 'code' => 'US-SALES-RED', 'rate' => 4.00],
        ['name' => 'US Local Sales Tax (2.5%)', 'code' => 'US-SALES-LOCAL', 'rate' => 2.50],
        ['name' => 'US No Sales Tax (0%)', 'code' => 'US-SALES-ZERO', 'rate' => 0.00],
    ];

    protected array $otherTaxRates = [
        // Standard rates by region/type
        ['name' => 'Standard VAT (20%)', 'code' => 'VAT-STD-20', 'rate' => 20.00],
        ['name' => 'Standard VAT (21%)', 'code' => 'VAT-STD-21', 'rate' => 21.00],
        ['name' => 'Standard VAT (22%)', 'code' => 'VAT-STD-22', 'rate' => 22.00],
        ['name' => 'Standard VAT (23%)', 'code' => 'VAT-STD-23', 'rate' => 23.00],
        ['name' => 'Standard VAT (24%)', 'code' => 'VAT-STD-24', 'rate' => 24.00],
        ['name' => 'Standard VAT (25%)', 'code' => 'VAT-STD-25', 'rate' => 25.00],

        // Reduced rates
        ['name' => 'Reduced Rate (5%)', 'code' => 'VAT-RED-5', 'rate' => 5.00],
        ['name' => 'Reduced Rate (6%)', 'code' => 'VAT-RED-6', 'rate' => 6.00],
        ['name' => 'Reduced Rate (7%)', 'code' => 'VAT-RED-7', 'rate' => 7.00],
        ['name' => 'Reduced Rate (10%)', 'code' => 'VAT-RED-10', 'rate' => 10.00],

        // Special rates
        ['name' => 'GST (5%)', 'code' => 'GST-5', 'rate' => 5.00],
        ['name' => 'GST (10%)', 'code' => 'GST-10', 'rate' => 10.00],
        ['name' => 'GST (15%)', 'code' => 'GST-15', 'rate' => 15.00],

        // Zero rates
        ['name' => 'Zero Rate (0%)', 'code' => 'ZERO-RATE', 'rate' => 0.00],

        // Special cases
        ['name' => 'Digital Services Tax', 'code' => 'DIGITAL-TAX', 'rate' => 3.00],
        ['name' => 'Tourism Tax', 'code' => 'TOURISM-TAX', 'rate' => 1.50],
        ['name' => 'Environmental Tax', 'code' => 'ENV-TAX', 'rate' => 0.50],
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $this->command->info("Seeding tax rates for company: {$company->name}");

            // Get existing tax rates for this company
            $existingRates = TaxRate::query()
                ->where('company_id', $company->id)
                ->pluck('code')
                ->toArray();

            $allRates = array_merge(
                $this->europeanVatRates,
                $this->usSalesTaxRates,
                $this->otherTaxRates
            );

            $created = 0;
            $skipped = 0;

            $bar = $this->command->getOutput()->createProgressBar(count($allRates));
            $bar->start();

            // Process European VAT rates
            foreach ($this->europeanVatRates as $rate) {
                if (in_array($rate['code'], $existingRates, true)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $this->createTaxRate([
                    'company_id'    => $company->id,
                    'name'          => $rate['name'],
                    'code'          => $rate['code'],
                    'rate'          => $rate['rate'],
                    'tax_rate_type' => TaxRateType::EXCLUSIVE->value,
                    'is_compound'   => false,
                    'calculate_vat' => true,
                    'is_active'     => true,
                ]);
                $created++;
                $bar->advance();
            }

            // Process US Sales Tax rates
            foreach ($this->usSalesTaxRates as $rate) {
                if (in_array($rate['code'], $existingRates, true)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $this->createTaxRate([
                    'company_id'    => $company->id,
                    'name'          => $rate['name'],
                    'code'          => $rate['code'],
                    'rate'          => $rate['rate'],
                    'tax_rate_type' => TaxRateType::INCLUSIVE->value,
                    'is_compound'   => false,
                    'calculate_vat' => false,
                    'is_active'     => true,
                ]);
                $created++;
                $bar->advance();
            }

            // Process other tax rates
            foreach ($this->otherTaxRates as $rate) {
                if (in_array($rate['code'], $existingRates, true)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $this->createTaxRate([
                    'company_id'    => $company->id,
                    'name'          => $rate['name'],
                    'code'          => $rate['code'],
                    'rate'          => $rate['rate'],
                    'tax_rate_type' => str_contains($rate['code'], 'GST') || str_contains($rate['code'], 'VAT')
                        ? TaxRateType::EXCLUSIVE->value
                        : TaxRateType::INCLUSIVE->value,
                    'is_compound'   => false,
                    'calculate_vat' => str_contains($rate['code'], 'VAT') || str_contains($rate['code'], 'GST'),
                    'is_active'     => true,
                ]);
                $created++;
                $bar->advance();
            }

            $bar->finish();
            $this->command->newLine(2);
            $this->command->info(sprintf(
                'Tax rates for %s: %d created, %d already existed',
                $company->name,
                $created,
                $skipped
            ));
        });
    }

    protected function createTaxRate(array $data): void
    {
        TaxRate::updateOrCreate(
            [
                'company_id' => $data['company_id'],
                'code'       => $data['code'],
            ],
            $data
        );
    }
}
