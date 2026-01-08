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

    public function createProductCategory(array $data): Model
    {
        return $this->create([
            'category_name' => $data['category_name'],
        ]);
    }

    public function updateProductCategory(ProductCategory $model, array $data): ProductCategory
    {
        $model->update([
            'category_name' => $data['category_name'],
        ]);

        return $model;
    }
}
