<?php

namespace Modules\Projects\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Projects\Models\Project;

class ProjectLegacyExporter extends Exporter
{
    protected static ?string $model = Project::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('project_name')
                ->label(trans('ip.project_name')),
            ExportColumn::make('client')
                ->label(trans('ip.client'))
                ->formatStateUsing(fn ($state, Project $record) => $record->relation?->trading_name ?? $record->relation?->company_name ?? ''),
            ExportColumn::make('project_status')
                ->label(trans('ip.project_status'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('start_at')
                ->label(trans('ip.start_at')),
            ExportColumn::make('end_at')
                ->label(trans('ip.end_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your project export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
