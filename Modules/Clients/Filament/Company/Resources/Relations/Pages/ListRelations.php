<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;

class ListRelations extends ListRecords
{
    protected static string $resource = RelationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('full'),
        ];
    }
}
