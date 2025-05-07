<?php

namespace Modules\Products\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Products\Models\ProductCategory;

class ProductCategoryService extends BaseService
{
    public function model(): string
    {
        return ProductCategory::class;
    }

    public function create(array $validatedInput): Model
    {
        $productCategory = ProductCategory::create(
            $validatedInput
        );

        $productCategory->save();

        return $productCategory;
    }

    public function update(array $validatedInput, $productCategoryToUpdate): Model
    {
        $productCategoryToUpdate->fill($validatedInput);

        $productCategoryToUpdate->save();

        return $productCategoryToUpdate;
    }
}
