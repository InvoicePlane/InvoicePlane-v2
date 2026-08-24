<?php

namespace Modules\Quotes\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Illuminate\Support\Carbon;
use Modules\Core\Filament\Exporters\BaseExporter;
use Modules\Quotes\Models\Quote;

class QuoteLegacyExporter extends BaseExporter
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
                ->label(trans('ip.quoted_at'))
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->toDateString() : ''),
            ExportColumn::make('quote_expires_at')
                ->label(trans('ip.quote_expires_at'))
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->toDateString() : ''),
            ExportColumn::make('quote_total')
                ->label(trans('ip.quote_total')),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.quote');
    }
}
