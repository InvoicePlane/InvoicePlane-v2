<?php

namespace Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\CreateCustomFieldValue;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource;

use Filament\Resources\Pages\CreateRecord;

class CreateCustomFieldValue extends CreateRecord
{
    protected static string $resource = CustomFieldValueResource::class;
}
