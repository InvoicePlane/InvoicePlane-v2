<?php

namespace Modules\Products\Services;

use Modules\Products\Models\ProductUnit;

use Modules\Products\Services\ProductUnitService;

use Modules\Core\Services\BaseService;

use Illuminate\Database\Eloquent\Model;

class ProductUnitService extends BaseService
{
    public function model(): string
    {
        return ProductUnit::class;
    }

    public function create(array $validatedInput): ProductUnit
    {
        $productUnit = ProductUnit::create(
            $validatedInput
        );

        $productUnit->save();

        return $productUnit;
    }

    public function update(array $validatedInput, $productUnitToUpdate): Model
    {
        $productUnitToUpdate->fill($validatedInput);

        $productUnitToUpdate->save();

        return $productUnitToUpdate;
    }
}
