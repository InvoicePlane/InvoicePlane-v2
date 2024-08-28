<?php

namespace Modules\Projects\Filament\Resources\TaskResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Projects\Filament\Resources\TaskResource;

class ManageTasks extends ManageRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
