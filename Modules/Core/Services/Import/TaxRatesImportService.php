<?php

namespace Modules\Core\Services\Import;

use Modules\Core\Enums\TaxRateType;
use Modules\Core\Models\TaxRate;

class TaxRatesImportService extends AbstractImportService
{
    public function getTables(): array
    {
        return ['ip_tax_rates'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['tax_rates']);

        $this->importTaxRates();

        return $this->stats;
    }

    private function importTaxRates(): void
    {
        $taxRates = $this->getImportData('ip_tax_rates');

        foreach ($taxRates as $v1TaxRate) {
            $v2TaxRate = TaxRate::firstOrCreate(
                [
                    'company_id' => $this->companyId,
                    'name'       => $v1TaxRate->tax_rate_name ?? 'Tax',
                    'rate'       => $v1TaxRate->tax_rate_percent ?? 0,
                ],
                [
                    'code'          => strtoupper(substr($v1TaxRate->tax_rate_name ?? 'TAX', 0, 10)),
                    'tax_rate_type' => TaxRateType::SALES,
                    'is_active'     => true,
                ]
            );

            $this->idMappings['tax_rates'][$v1TaxRate->tax_rate_id] = $v2TaxRate->id;
            $this->stats['tax_rates']++;
        }
    }
}
