<?php

namespace Modules\Projects\Filament\Company\Resources\Projects\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Projects\Filament\Company\Resources\Projects\ProjectResource;
use Modules\Projects\Services\ProjectExportService;

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

            ActionGroup::make([
                Action::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(ProjectExportService::class)->export('csv')),
                Action::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(ProjectExportService::class)->exportWithVersion('csv', 1)),
                Action::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(ProjectExportService::class)->export('xlsx')),
                Action::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(ProjectExportService::class)->exportWithVersion('xlsx', 1)),
            ])
                ->label('Export')
                ->icon('heroicon-o-folder-arrow-down')
                ->button(),
        ];
    }
}
