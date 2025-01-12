<?php

namespace Modules\Inventory\Filament\Resources\ProductInventoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Inventory\Filament\Resources\ProductInventoryResource;

class ManageProductInventories extends ManageRecords
{
    protected static string $resource = ProductInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
