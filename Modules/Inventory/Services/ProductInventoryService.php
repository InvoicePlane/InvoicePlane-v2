<?php

namespace Modules\Inventory\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Inventory\Events\ProductInventoryWasCreated;
use Modules\Inventory\Events\ProductInventoryWasUpdated;
use Modules\Inventory\Models\ProductInventory;

class ProductInventoryService extends BaseService
{
    public function model(): string
    {
        return ProductInventory::class;
    }

    public function create(array $validatedInput): ProductInventory
    {
        $productInventory = new ProductInventory(
            $validatedInput
        );

        $productInventory->save();

        event(new ProductInventoryWasCreated($productInventory));

        return $productInventory;
    }

    public function update(array $validatedInput, $productToUpdate): Model
    {
        $productToUpdate->fill($validatedInput);

        $productToUpdate->save();

        event(new ProductInventoryWasUpdated($productToUpdate));

        return $productToUpdate;
    }
}
