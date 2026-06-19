<?php

namespace Modules\Products\Filament\Resources\ProductFamilyResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Products\Filament\Resources\ProductFamilyResource;

class ManageProductFamilies extends ManageRecords
{
    protected static string $resource = ProductFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
