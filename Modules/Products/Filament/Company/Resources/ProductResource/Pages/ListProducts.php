<?php

namespace Modules\Products\Filament\Company\Resources\ProductResource\Pages;

use Modules\Products\Filament\Company\Resources\ProductResource\Pages\ListProducts;

use Modules\Products\Filament\Company\Resources\ProductResource;

use Modules\Core\Models\Company;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
