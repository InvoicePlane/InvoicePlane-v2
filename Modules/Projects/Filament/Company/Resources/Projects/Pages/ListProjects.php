<?php

namespace Modules\Projects\Filament\Company\Resources\Projects\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Projects\Filament\Company\Resources\Projects\ProjectResource;
use Modules\Projects\Filament\Exporters\ProjectExporter;
use Modules\Projects\Filament\Exporters\ProjectLegacyExporter;

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
                ExportAction::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(ProjectExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(ProjectLegacyExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->exporter(ProjectExporter::class)
                    ->formats([ExportFormat::Xlsx]),
                ExportAction::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->exporter(ProjectLegacyExporter::class)
                    ->formats([ExportFormat::Xlsx]),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
