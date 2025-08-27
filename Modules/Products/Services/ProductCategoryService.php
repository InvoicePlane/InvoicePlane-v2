<?php

namespace Modules\Products\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Products\Models\ProductCategory;
use Throwable;

class ProductCategoryService extends BaseService
{
    public function model(): string
    {
        return ProductCategory::class;
    }

    public function createProductCategory(array $data): Model
    {
        return ProductCategory::query()->create([
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

    public function deleteProductCategory(ProductCategory $category, array $data = []): ProductCategory
    {
        DB::beginTransaction();
        try {
            $category->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $category;
    }
}
