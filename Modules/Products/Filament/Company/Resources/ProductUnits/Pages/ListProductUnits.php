<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnits\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Products\Filament\Company\Resources\ProductUnits\ProductUnitResource;

class ListProductUnits extends ListRecords
{
    protected static string $resource = ProductUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('full'),
        ];
    }
}
