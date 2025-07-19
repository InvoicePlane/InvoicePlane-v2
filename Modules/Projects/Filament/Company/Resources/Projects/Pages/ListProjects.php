<?php

namespace Modules\Projects\Filament\Company\Resources\Projects\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Projects\Filament\Company\Resources\Projects\ProjectResource;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(\Modules\Projects\Services\ProjectService::class)->createProject($data);
                })
                ->modalWidth('full'),
        ];
    }
}
