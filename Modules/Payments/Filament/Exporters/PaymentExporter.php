<?php

namespace Modules\Payments\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Illuminate\Support\Carbon;
use Modules\Core\Filament\Exporters\BaseExporter;
use Modules\Payments\Models\Payment;

class PaymentExporter extends BaseExporter
{
    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('payment_method')
                ->label(trans('ip.payment_method'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('payment_status')
                ->label(trans('ip.payment_status'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('customer_name')
                ->label(trans('ip.customer_name'))
                ->formatStateUsing(fn ($state, Payment $record) => $record->customer?->trading_name ?? $record->customer?->company_name ?? ''),
            ExportColumn::make('payment_amount')
                ->label(trans('ip.payment_amount')),
            ExportColumn::make('paid_at')
                ->label(trans('ip.paid_at'))
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->toDateString() : ''),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.payment');
    }
}
