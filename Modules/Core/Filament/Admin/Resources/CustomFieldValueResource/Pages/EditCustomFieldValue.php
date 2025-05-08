<?php

namespace Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource;

class EditCustomFieldValue extends EditRecord
{
    protected static string $resource = CustomFieldValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
