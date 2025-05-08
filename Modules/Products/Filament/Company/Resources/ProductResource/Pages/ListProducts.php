<?php

namespace Modules\Products\Filament\Company\Resources\ProductResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Products\Filament\Company\Resources\ProductResource;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
