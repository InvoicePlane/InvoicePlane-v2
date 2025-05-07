<?php

namespace Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages;

use Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages\CreateCustomField;

use Modules\Core\Filament\Admin\Resources\CustomFieldResource;

use Filament\Resources\Pages\CreateRecord;

class CreateCustomField extends CreateRecord
{
    protected static string $resource = CustomFieldResource::class;
}
