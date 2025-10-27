<?php

namespace Modules\Projects\Filament\Company\Resources\Tasks\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Projects\Filament\Company\Resources\Tasks\TaskResource;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskExportService;
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
                Action::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(TaskExportService::class)->export('csv')),
                Action::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(TaskExportService::class)->exportWithVersion('csv', 1)),
                Action::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(TaskExportService::class)->export('xlsx')),
                Action::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(TaskExportService::class)->exportWithVersion('xlsx', 1)),
            ])
                ->label('Export')
                ->icon('heroicon-o-folder-arrow-down')
                ->button(),
        ];
    }

    /** @phpstan-ignore-next-line */
    protected function getTableQuery(): Builder|Relation|null
    {
        return Task::query()
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
    }
}
