<?php

namespace Modules\Clients\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Clients\Models\Relation;

class RelationLegacyExporter extends Exporter
{
    protected static ?string $model = Relation::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('relation_type')
                ->label(trans('ip.relation_type'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('trading_name')
                ->label(trans('ip.trading_name'))
                ->formatStateUsing(fn ($state, Relation $record) => $record->trading_name ?? $record->company_name),
            ExportColumn::make('email')
                ->label(trans('ip.email')),
            ExportColumn::make('phone')
                ->label(trans('ip.phone')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your relation export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
