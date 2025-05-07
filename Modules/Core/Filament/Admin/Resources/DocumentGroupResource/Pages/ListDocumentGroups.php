<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\DocumentGroupResource;

class ListDocumentGroups extends ListRecords
{
    protected static string $resource = DocumentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
