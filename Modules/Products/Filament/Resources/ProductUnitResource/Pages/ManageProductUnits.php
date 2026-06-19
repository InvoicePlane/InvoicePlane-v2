<?php

namespace Modules\Products\Filament\Resources\ProductUnitResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Products\Filament\Resources\ProductUnitResource;

class ManageProductUnits extends ManageRecords
{
    protected static string $resource = ProductUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
