<?php

namespace Modules\Projects\Filament\Resources\ProjectResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Projects\Filament\Resources\ProjectResource;

class ManageProjects extends ManageRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
