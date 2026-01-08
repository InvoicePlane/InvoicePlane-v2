<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\DocumentGroupResource;
use Modules\Core\Services\DocumentGroupService;

class ListDocumentGroups extends ListRecords
{
    protected static string $resource = DocumentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(DocumentGroupService::class)->createDocumentGroup($data);
                })
                ->modalWidth('full'),
        ];
    }
}
