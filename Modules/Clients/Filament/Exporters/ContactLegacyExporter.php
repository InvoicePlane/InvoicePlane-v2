<?php

namespace Modules\Clients\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Clients\Models\Contact;

class ContactLegacyExporter extends Exporter
{
    protected static ?string $model = Contact::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('relation_id')
                ->label(trans('ip.relation_id'))
                ->formatStateUsing(fn ($state, Contact $record) => $record->relation?->trading_name ?? $record->relation?->company_name ?? ''),
            ExportColumn::make('type')
                ->label(trans('ip.type'))
                ->formatStateUsing(fn ($state, Contact $record) => $record->relation?->relation_type?->label() ?? ''),
            ExportColumn::make('full_name')
                ->label(trans('ip.contact_name')),
            ExportColumn::make('email')
                ->label(trans('ip.email')),
            ExportColumn::make('phone')
                ->label(trans('ip.phone')),
            ExportColumn::make('gender')
                ->label(trans('ip.gender')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your contact export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
