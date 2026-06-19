<?php

namespace Modules\Products\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Products\Models\ProductFamily;

class ProductFamilyService extends BaseService
{
    public function model(): string
    {
        return ProductFamily::class;
    }

    public function create(array $validatedInput): ProductFamily
    {
        $productFamily = ProductFamily::create(
            $validatedInput
        );

        $productFamily->save();

        return $productFamily;
    }

    public function update(array $validatedInput, $productFamilyToUpdate): Model
    {
        $productFamilyToUpdate->fill($validatedInput);

        $productFamilyToUpdate->save();

        return $productFamilyToUpdate;
    }
}
