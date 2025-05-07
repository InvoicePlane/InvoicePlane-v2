<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductUnitResource;

use Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages\ListProductUnits;

use Modules\Core\Models\Company;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductUnits extends ListRecords
{
    protected static string $resource = ProductUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
