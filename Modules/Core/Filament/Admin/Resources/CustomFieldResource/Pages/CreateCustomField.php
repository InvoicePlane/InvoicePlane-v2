<?php

namespace Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Admin\Resources\CustomFieldResource;

class CreateCustomField extends CreateRecord
{
    protected static string $resource = CustomFieldResource::class;
}
