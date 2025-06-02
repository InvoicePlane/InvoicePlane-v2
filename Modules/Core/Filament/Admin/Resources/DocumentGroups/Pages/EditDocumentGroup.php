<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\DocumentGroupResource;

class EditDocumentGroup extends EditRecord
{
    protected static string $resource = DocumentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
