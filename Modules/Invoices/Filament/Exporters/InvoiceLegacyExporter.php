<?php

namespace Modules\Invoices\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Invoices\Models\Invoice;

class InvoiceLegacyExporter extends Exporter
{
    protected static ?string $model = Invoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('invoice_status')
                ->label(trans('ip.invoice_status'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('invoice_number')
                ->label(trans('ip.invoice_number')),
            ExportColumn::make('customer_name')
                ->label(trans('ip.customer_name'))
                ->formatStateUsing(fn ($state, Invoice $record) => $record->customer?->trading_name ?? $record->customer?->company_name ?? ''),
            ExportColumn::make('invoice_total')
                ->label(trans('ip.invoice_total')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your invoice export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
