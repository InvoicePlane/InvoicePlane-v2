<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Products\Filament\Company\Resources\ProductUnitResource;

class ListProductUnits extends ListRecords
{
    protected static string $resource = ProductUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
