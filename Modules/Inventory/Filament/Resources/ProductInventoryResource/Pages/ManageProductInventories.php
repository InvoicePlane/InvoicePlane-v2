<?php

namespace Modules\Products\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Products\Filament\Resources\ProductResource;

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
