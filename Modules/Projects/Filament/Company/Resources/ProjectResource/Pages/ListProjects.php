<?php

namespace Modules\Projects\Filament\Company\Resources\ProjectResource\Pages;

use Modules\Projects\Filament\Company\Resources\ProjectResource\Pages\ListProjects;

use Modules\Core\Models\Company;

use Modules\Projects\Filament\Company\Resources\ProjectResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
