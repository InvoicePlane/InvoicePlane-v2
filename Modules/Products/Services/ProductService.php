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

    public function createProduct(array $data): Model
    {
        return Product::query()->create([
            'company_id'     => $this->getCompanyId(),
            'category_id'    => $data['category_id'],
            'unit_id'        => $data['unit_id'] ?? null,
            'type'           => $data['type'],
            'code'           => $data['code'],
            'product_name'   => $data['product_name'],
            'price'          => $data['price'],
            'cost_price'     => $data['cost_price'] ?? null,
            'product_tariff' => $data['product_tariff'] ?? null,
            'tax_rate_id'    => $data['tax_rate_id'] ?? null,
            'tax_rate_2_id'  => $data['tax_rate_2_id'] ?? null,
            'description'    => $data['description'] ?? null,
        ]);
    }

    public function updateProduct(Product $model, array $data): Product
    {
        $model->update([
            'company_id'     => $this->getCompanyId(),
            'category_id'    => $data['category_id'],
            'unit_id'        => $data['unit_id'] ?? null,
            'type'           => $data['type'],
            'code'           => $data['code'],
            'product_name'   => $data['product_name'],
            'price'          => $data['price'],
            'cost_price'     => $data['cost_price'] ?? null,
            'product_tariff' => $data['product_tariff'] ?? null,
            'tax_rate_id'    => $data['tax_rate_id'] ?? null,
            'tax_rate_2_id'  => $data['tax_rate_2_id'] ?? null,
            'description'    => $data['description'] ?? null,
        ]);

        return $model;
    }
}
