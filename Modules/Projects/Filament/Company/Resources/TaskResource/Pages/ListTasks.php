<?php

namespace Modules\Projects\Filament\Company\Resources\TaskResource\Pages;

use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\ListTasks;

use Modules\Projects\Filament\Company\Resources\TaskResource;

use Modules\Core\Models\Company;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
