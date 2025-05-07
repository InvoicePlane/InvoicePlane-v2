<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\DocumentGroupResource;

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
