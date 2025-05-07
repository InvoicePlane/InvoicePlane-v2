<?php

namespace Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\EditCustomFieldValue;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomFieldValue extends EditRecord
{
    protected static string $resource = CustomFieldValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
