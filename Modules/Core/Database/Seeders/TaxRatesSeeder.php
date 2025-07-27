<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Support\Facades\DB;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Models\TaxRate;

class TaxRatesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    protected array $europeanVatRates = [
        // Austria
        ['country' => 'AT', 'name' => 'AT Standard VAT', 'code' => 'VAT-AT-STD', 'rate' => 20.00],
        ['country' => 'AT', 'name' => 'AT Reduced 1', 'code' => 'VAT-AT-RED1', 'rate' => 10.00],
        ['country' => 'AT', 'name' => 'AT Reduced 2', 'code' => 'VAT-AT-RED2', 'rate' => 13.00],

        // Belgium
        ['country' => 'BE', 'name' => 'BE Standard VAT', 'code' => 'VAT-BE-STD', 'rate' => 21.00],
        ['country' => 'BE', 'name' => 'BE Reduced 1', 'code' => 'VAT-BE-RED1', 'rate' => 6.00],
        ['country' => 'BE', 'name' => 'BE Reduced 2', 'code' => 'VAT-BE-RED2', 'rate' => 12.00],

        // Germany
        ['country' => 'DE', 'name' => 'DE Standard VAT', 'code' => 'VAT-DE-STD', 'rate' => 19.00],
        ['country' => 'DE', 'name' => 'DE Reduced', 'code' => 'VAT-DE-RED', 'rate' => 7.00],

        // France
        ['country' => 'FR', 'name' => 'FR Standard VAT', 'code' => 'VAT-FR-STD', 'rate' => 20.00],
        ['country' => 'FR', 'name' => 'FR Reduced 1', 'code' => 'VAT-FR-RED1', 'rate' => 5.50],
        ['country' => 'FR', 'name' => 'FR Reduced 2', 'code' => 'VAT-FR-RED2', 'rate' => 10.00],

        // Netherlands
        ['country' => 'NL', 'name' => 'NL Standard VAT', 'code' => 'VAT-NL-STD', 'rate' => 21.00],
        ['country' => 'NL', 'name' => 'NL Reduced', 'code' => 'VAT-NL-RED', 'rate' => 9.00],

        // Spain
        ['country' => 'ES', 'name' => 'ES Standard VAT', 'code' => 'VAT-ES-STD', 'rate' => 21.00],
        ['country' => 'ES', 'name' => 'ES Reduced 1', 'code' => 'VAT-ES-RED1', 'rate' => 10.00],
        ['country' => 'ES', 'name' => 'ES Reduced 2', 'code' => 'VAT-ES-RED2', 'rate' => 4.00],

        // Italy
        ['country' => 'IT', 'name' => 'IT Standard VAT', 'code' => 'VAT-IT-STD', 'rate' => 22.00],
        ['country' => 'IT', 'name' => 'IT Reduced 1', 'code' => 'VAT-IT-RED1', 'rate' => 10.00],
        ['country' => 'IT', 'name' => 'IT Reduced 2', 'code' => 'VAT-IT-RED2', 'rate' => 5.00],

        // United Kingdom
        ['country' => 'GB', 'name' => 'GB Standard VAT', 'code' => 'VAT-GB-STD', 'rate' => 20.00],
        ['country' => 'GB', 'name' => 'GB Reduced', 'code' => 'VAT-GB-RED', 'rate' => 5.00],
        ['country' => 'GB', 'name' => 'GB Zero', 'code' => 'VAT-GB-ZERO', 'rate' => 0.00],
    ];

    protected array $usSalesTaxRates = [
        // United States - State sales tax rates (approximate, as they vary by locality)
        ['state' => 'AL', 'name' => 'AL Sales Tax', 'code' => 'US-AL-TAX', 'rate' => 4.00],
        ['state' => 'AK', 'name' => 'AK Sales Tax', 'code' => 'US-AK-TAX', 'rate' => 0.00],
        ['state' => 'AZ', 'name' => 'AZ Sales Tax', 'code' => 'US-AZ-TAX', 'rate' => 5.60],
        ['state' => 'AR', 'name' => 'AR Sales Tax', 'code' => 'US-AR-TAX', 'rate' => 6.50],
        ['state' => 'CA', 'name' => 'CA Sales Tax', 'code' => 'US-CA-TAX', 'rate' => 7.25],
    ];

    protected array $otherTaxRates = [
        // Common tax rates for other countries
        ['country' => 'US', 'name' => 'US Sales Tax', 'code' => 'US-SALES-TAX', 'rate' => 7.00],
        ['country' => 'CA', 'name' => 'CA GST/HST', 'code' => 'CA-GST', 'rate' => 5.00],
        ['country' => 'CA', 'name' => 'CA HST', 'code' => 'CA-HST', 'rate' => 13.00], // Ontario HST
        ['country' => 'AU', 'name' => 'AU GST', 'code' => 'AU-GST', 'rate' => 10.00],
        ['country' => 'NZ', 'name' => 'NZ GST', 'code' => 'NZ-GST', 'rate' => 15.00],
        ['country' => 'JP', 'name' => 'JP Consumption Tax', 'code' => 'JP-CONSUMP', 'rate' => 10.00],
        ['country' => 'CH', 'name' => 'CH VAT', 'code' => 'CH-VAT', 'rate' => 7.70],
        ['country' => 'NO', 'name' => 'NO VAT', 'code' => 'NO-VAT', 'rate' => 25.00],
    ];

    public function run(?int $companyId = null): void
    {
        $companyId = $companyId ?: $this->command->argument('company_id') ?? 1;

        if ( ! DB::table('companies')->where('id', $companyId)->exists()) {
            $this->command->error("Company with ID {$companyId} does not exist.");

            return;
        }

        $this->command->info('Seeding tax rates...');
        $bar = $this->command->getOutput()->createProgressBar(
            count($this->europeanVatRates) +
            count($this->usSalesTaxRates) +
            count($this->otherTaxRates)
        );

        foreach ($this->europeanVatRates as $rate) {
            $this->createTaxRate([
                'company_id'    => $companyId,
                'name'          => $rate['name'],
                'code'          => $rate['code'],
                'rate'          => $rate['rate'],
                'tax_rate_type' => TaxRateType::EXCLUSIVE->value,
                'is_compound'   => false,
                'calculate_vat' => true,
                'is_active'     => true,
            ]);
            $bar->advance();
        }

        foreach ($this->usSalesTaxRates as $rate) {
            $this->createTaxRate([
                'company_id'    => $companyId,
                'name'          => $rate['name'],
                'code'          => $rate['code'],
                'rate'          => $rate['rate'],
                'tax_rate_type' => TaxRateType::INCLUSIVE->value,
                'is_compound'   => false,
                'calculate_vat' => false,
                'is_active'     => true,
            ]);
            $bar->advance();
        }

        foreach ($this->otherTaxRates as $rate) {
            $this->createTaxRate([
                'company_id'    => $companyId,
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
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info('Tax rates seeded successfully!');
    }

    protected function createTaxRate(array $data): TaxRate
    {
        $taxRate = TaxRate::query()->firstOrNew([
            'company_id' => $data['company_id'],
            'code'       => $data['code'],
        ]);

        if ( ! $taxRate->exists) {
            $taxRate->fill([
                'name'          => $data['name'],
                'rate'          => $data['rate'],
                'tax_rate_type' => $data['tax_rate_type'],
                'is_compound'   => $data['is_compound'] ?? false,
                'calculate_vat' => $data['calculate_vat'] ?? false,
                'is_active'     => $data['is_active'] ?? true,
            ])->save();
        }

        return $taxRate;
    }
}
