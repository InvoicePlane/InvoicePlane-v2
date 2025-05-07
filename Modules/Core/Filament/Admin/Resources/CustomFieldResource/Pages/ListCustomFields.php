<?php

namespace Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages;

use Modules\Core\Filament\Admin\Resources\CustomFieldResource\Pages\ListCustomFields;

use Modules\Core\Filament\Admin\Resources\CustomFieldResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\CustomFieldResource;

class ListCustomFields extends ListRecords
{
    protected static string $resource = CustomFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
