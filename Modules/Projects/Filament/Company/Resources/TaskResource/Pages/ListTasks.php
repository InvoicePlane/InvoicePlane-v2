<?php

namespace Modules\Projects\Filament\Company\Resources\TaskResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Projects\Filament\Company\Resources\TaskResource;

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
