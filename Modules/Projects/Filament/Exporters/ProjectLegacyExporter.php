<?php

namespace Modules\Projects\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Projects\Models\Project;
use Modules\Core\Filament\Exporters\BaseExporter;

class ProjectLegacyExporter extends BaseExporter
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
                ->label(trans('ip.start_at'))
                ->date(),
            ExportColumn::make('end_at')
                ->label(trans('ip.end_at'))
                ->date(),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.project');
    }
}
