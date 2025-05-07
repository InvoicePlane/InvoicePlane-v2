<?php

namespace Modules\Products\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Products\Models\ProductUnit;

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
