<?php

namespace Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource\Pages\ListCustomFieldValues;

use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\CustomFieldValueResource;

class ListCustomFieldValues extends ListRecords
{
    protected static string $resource = CustomFieldValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
