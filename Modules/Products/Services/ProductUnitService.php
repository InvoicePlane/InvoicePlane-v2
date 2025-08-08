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
        return ProductUnit::query()->create([
            'unit_name'      => $data['unit_name'],
            'unit_name_plrl' => $data['unit_name_plrl'],
        ]);
    }

    public function updateProductUnit(ProductUnit $model, array $data): ProductUnit
    {
        $model->update([
            'unit_name'      => $data['unit_name'],
            'unit_name_plrl' => $data['unit_name_plrl'],
        ]);

        return $model;
    }
}
