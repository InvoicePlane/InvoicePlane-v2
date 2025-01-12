<?php

namespace Modules\Products\Filament\Resources\ProductResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Products\Filament\Resources\ProductResource;

class CreateProductInventory extends CreateRecord
{
    protected static string $resource = ProductInventoryResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
