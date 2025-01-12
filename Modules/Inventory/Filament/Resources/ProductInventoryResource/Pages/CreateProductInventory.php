<?php

namespace Modules\Inventory\Filament\Resources\ProductInventoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Inventory\Filament\Resources\ProductInventoryResource;

class CreateProductInventory extends CreateRecord
{
    protected static string $resource = ProductInventoryResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
