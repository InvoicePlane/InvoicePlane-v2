<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\TaxRate;

class TaxRateService extends BaseService
{
    public function model(): string
    {
        return TaxRate::class;
    }

    public function createTaxRate(array $data): Model
    {
        $taxRate = $this->create([
            'company_id'    => $this->getCompanyId(),
            'tax_rate_type' => $data['tax_rate_type'] ?? null,
            'is_active'     => $data['is_active'] ?? false,
            'code'          => $data['code'] ?? null,
            'name'          => $data['name'],
            'rate'          => $data['rate'] ?? null,
        ]);

        return $taxRate;
    }

    public function updateTaxRate($taxRate, array $data): Model
    {
        $updateData = [
            'name' => $data['name'],
        ];

        $taxRate->update($updateData);

        return $taxRate;
    }
}
