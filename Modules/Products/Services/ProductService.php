<?php

namespace Modules\Products\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Products\Models\Product;

class ProductService extends BaseService
{
    public function model(): string
    {
        return Product::class;
        //event(new ProductWasCreated($product));
        //event(new ProductWasUpdated($productToUpdate));
    }

    public function create(array $data): Product
    {
        return Product::create([
            'product_name' => $data['product_name'],
            'product_sku'  => $data['product_sku'],
            'price'        => $data['price'],
            'description'  => $data['description'] ?? null,
            'unit_id'      => $data['unit_id'] ?? null,
        ]);
    }

    public function update(array $data, $model): Model
    {
        $model->update([
            'product_name' => $data['product_name'],
            'product_sku'  => $data['product_sku'],
            'price'        => $data['price'],
            'description'  => $data['description'] ?? null,
            'unit_id'      => $data['unit_id'] ?? null,
        ]);

        return $model;
    }
}
