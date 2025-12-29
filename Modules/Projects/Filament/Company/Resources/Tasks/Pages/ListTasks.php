<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Projects\Filament\Company\Resources\Tasks\TaskResource;
use Modules\Projects\Filament\Exporters\TaskExporter;
use Modules\Projects\Filament\Exporters\TaskLegacyExporter;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskService;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data) {
                    app(TaskService::class)->createTask($data);
                })->modalWidth('full'),

            ActionGroup::make([
                ExportAction::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(TaskExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(TaskLegacyExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->exporter(TaskExporter::class)
                    ->formats([ExportFormat::Xlsx]),
                ExportAction::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->exporter(TaskLegacyExporter::class)
                    ->formats([ExportFormat::Xlsx]),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        $query = Task::query()
            ->orderByRaw("
                FIELD(task_status,
                    'not_started',
                    'open',
                    'in_progress',
                    'completed',
                    'paid',
                    'cancelled'
                )
            ")
            ->orderBy('due_at', 'asc');

        /* @var Builder $query */
        return $query;
    }
}
