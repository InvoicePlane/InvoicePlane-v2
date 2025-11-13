<?php

namespace Modules\Projects\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Projects\Models\Task;

class TaskLegacyExporter extends Exporter
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
                ->label(trans('ip.task_finish_date')),
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

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your task export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
