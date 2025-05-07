<?php

namespace Modules\Products\Services;

use Modules\Products\Events\ProductWasUpdated;

use Modules\Products\Models\ProductUnit;

use Modules\Products\Services\ProductService;

use Modules\Products\Events\ProductWasCreated;

use Modules\Core\Models\TaxRate;

use Modules\Products\Models\ProductCategory;

use Modules\Products\Models\Product;

use Modules\Core\Services\BaseService;

use Illuminate\Database\Eloquent\Model;


class ProductService extends BaseService
{
    public function model(): string
    {
        return Product::class;
    }

    public function create(array $validatedInput): Product
    {
        $productCategory = ProductCategory::findOrFail($validatedInput['family_id']);
        $productUnit     = ProductUnit::findOrFail($validatedInput['unit_id']);
        $taxRate         = TaxRate::findOrFail($validatedInput['tax_rate_id']);

        $product = new Product(
            $validatedInput
        );
        $product->productCategory()->associate($productCategory);
        $product->productUnit()->associate($productUnit);
        $product->taxRate()->associate($taxRate);

        $product->save();

        event(new ProductWasCreated($product));

        return $product;
    }

    public function update(array $validatedInput, $productToUpdate): Model
    {
        $productToUpdate->fill($validatedInput);

        $productToUpdate->save();

        event(new ProductWasUpdated($productToUpdate));

        return $productToUpdate;
    }
}
