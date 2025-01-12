<?php

namespace Modules\Products\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\TaxRate;
use Modules\Core\Services\BaseService;
use Modules\Inventory\Events\ProductInventoryWasCreated;
use Modules\Inventory\Events\ProductInventoryWasUpdated;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductFamily;
use Modules\Products\Models\ProductUnit;

class ProductService extends BaseService
{
    public function model(): string
    {
        return Product::class;
    }

    public function create(array $validatedInput): Product
    {
        $productFamily = ProductFamily::findOrFail($validatedInput['family_id']);
        $productUnit = ProductUnit::findOrFail($validatedInput['unit_id']);
        $taxRate = TaxRate::findOrFail($validatedInput['tax_rate_id']);

        $product = new Product(
            $validatedInput
        );
        $product->productFamily()->associate($productFamily);
        $product->productUnit()->associate($productUnit);
        $product->taxRate()->associate($taxRate);

        $product->save();

        event(new ProductInventoryWasCreated($product));

        return $product;
    }

    public function update(array $validatedInput, $productToUpdate): Model
    {
        $productToUpdate->fill($validatedInput);

        $productToUpdate->save();

        event(new ProductInventoryWasUpdated($productToUpdate));

        return $productToUpdate;
    }
}
