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

    public function createProductUnit(array $data): Model
    {
        return $this->create([
            'name' => $data['name'],
        ]);
    }

    public function updateProductUnit(ProductUnit $model, array $data): ProductUnit
    {
        $model->update([
            'name' => $data['name'],
        ]);

        return $model;
    }
}
