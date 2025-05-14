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
            'unit_name' => $data['unit_name'],
        ]);
    }

    public function updateProductUnit(ProductUnit $model, array $data): ProductUnit
    {
        $model->update([
            'unit_name' => $data['unit_name'],
        ]);

        return $model;
    }
}
