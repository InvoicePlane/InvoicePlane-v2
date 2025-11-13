<?php

namespace Modules\Quotes\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Quotes\Models\Quote;

class QuoteLegacyExporter extends Exporter
{
    protected static ?string $model = Quote::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('quote_status')
                ->label(trans('ip.quote_status'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('quote_number')
                ->label(trans('ip.quote_number')),
            ExportColumn::make('prospect_name')
                ->label(trans('ip.prospect_name'))
                ->formatStateUsing(fn ($state, Quote $record) => $record->prospect?->trading_name ?? $record->prospect?->company_name ?? ''),
            ExportColumn::make('quoted_at')
                ->label(trans('ip.quoted_at')),
            ExportColumn::make('quote_expires_at')
                ->label(trans('ip.quote_expires_at')),
            ExportColumn::make('quote_total')
                ->label(trans('ip.quote_total')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your quote export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
