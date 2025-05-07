<?php

namespace Modules\Core\Services;

use Modules\Core\Models\TaxRate;

use Modules\Core\Services\TaxRateService;

use Modules\Core\Services\BaseService;

use Illuminate\Database\Eloquent\Model;

class TaxRateService extends BaseService
{
    public function model(): string
    {
        return TaxRate::class;
    }

    public function create(array $validatedInput): TaxRate
    {
        $taxRate = new TaxRate(
            $validatedInput
        );

        $taxRate->save();

        return $taxRate;
    }

    public function update(array $validatedInput, $taxRateToUpdate): Model
    {
        $taxRateToUpdate->fill($validatedInput);

        $taxRateToUpdate->save();

        return $taxRateToUpdate;
    }

    public function destroy(TaxRate $taxRate): ?bool
    {
        return $taxRate->delete();
    }
}
