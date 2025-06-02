<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\DocumentGroupResource;

class ListDocumentGroups extends ListRecords
{
    protected static string $resource = DocumentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('full'),
        ];
    }
}
