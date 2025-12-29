<?php

namespace Modules\Invoices\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Core\Filament\Exporters\BaseExporter;
use Modules\Invoices\Models\Invoice;

class InvoiceLegacyExporter extends BaseExporter
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

    protected static function getEntityName(): string
    {
        return trans('ip.invoice');
    }
}
