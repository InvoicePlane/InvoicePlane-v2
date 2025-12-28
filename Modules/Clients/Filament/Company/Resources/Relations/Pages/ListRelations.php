<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Clients\Filament\Company\Resources\Relations\RelationResource;
use Modules\Clients\Filament\Exporters\RelationExporter;
use Modules\Clients\Filament\Exporters\RelationLegacyExporter;
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
                ExportAction::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(RelationExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(RelationLegacyExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->exporter(RelationExporter::class)
                    ->formats([ExportFormat::Xlsx]),
                ExportAction::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->exporter(RelationLegacyExporter::class)
                    ->formats([ExportFormat::Xlsx]),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
