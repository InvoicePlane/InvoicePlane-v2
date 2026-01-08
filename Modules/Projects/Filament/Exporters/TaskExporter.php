<?php

namespace Modules\Projects\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Core\Filament\Exporters\BaseExporter;
use Modules\Projects\Models\Task;

class TaskExporter extends BaseExporter
{
    protected static ?string $model = Task::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('task_status')
                ->label(trans('ip.task_status'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('task_name')
                ->label(trans('ip.task_name')),
            ExportColumn::make('due_at')
                ->label(trans('ip.task_finish_date'))
                ->date(),
            ExportColumn::make('task_price')
                ->label(trans('ip.task_price')),
            ExportColumn::make('project_name')
                ->label(trans('ip.project_name'))
                ->formatStateUsing(fn ($state, Task $record) => $record->project?->project_name ?? ''),
            ExportColumn::make('customer_name')
                ->label(trans('ip.customer_name'))
                ->formatStateUsing(fn ($state, Task $record) => $record->relation?->trading_name ?? $record->relation?->company_name ?? ''),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.task');
    }
}
