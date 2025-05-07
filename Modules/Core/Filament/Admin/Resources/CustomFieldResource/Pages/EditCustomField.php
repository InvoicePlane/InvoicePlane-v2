<?php

namespace Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages;

use Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages\EditCustomField;

use Modules\Core\Filament\Admin\Resources\CustomFieldResource;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\CustomFieldResource;

class EditCustomField extends EditRecord
{
    protected static string $resource = CustomFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
