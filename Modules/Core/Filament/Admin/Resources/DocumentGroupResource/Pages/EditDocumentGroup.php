<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages;

use Modules\Core\Filament\Admin\Resources\DocumentGroupResource;

use Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages\EditDocumentGroup;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentGroup extends EditRecord
{
    protected static string $resource = DocumentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
