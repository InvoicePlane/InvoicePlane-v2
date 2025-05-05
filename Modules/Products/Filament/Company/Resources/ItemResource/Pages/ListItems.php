<?php

namespace Modules\Products\Filament\Company\Resources\ItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Products\Filament\Company\Resources\ItemResource;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
