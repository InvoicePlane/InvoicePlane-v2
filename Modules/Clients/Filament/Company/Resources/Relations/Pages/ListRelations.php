<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Clients\Services\RelationExportService;
use Modules\Clients\Services\RelationService;

class ListRelations extends ListRecords
{
    protected static string $resource = RelationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(RelationService::class)->createRelation($data);
                })
                ->modalWidth('full'),

            ActionGroup::make([
                Action::make('exportCsv')
                    ->label('Export as CSV')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        return app(RelationExportService::class)->export('csv');
                    }),
                Action::make('exportExcel')
                    ->label('Export as Excel')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        return app(RelationExportService::class)->export('xlsx');
                    }),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
