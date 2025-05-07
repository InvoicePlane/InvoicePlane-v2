<?php

namespace Modules\Projects\Filament\Company\Resources\ProjectResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Projects\Filament\Company\Resources\ProjectResource;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
