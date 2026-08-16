<?php

namespace Modules\Core\Filament\Company\Pages\Reports;

use BackedEnum;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Payments\Models\Payment;

class PaymentHistoryReport extends BaseTabularReportPage
{
    public static function getNavigationLabel(): string
    {
        return trans('ip.report_payment_history');
    }

    public function getTitle(): string
    {
        return trans('ip.report_payment_history');
    }

    public function reportQuery(): Builder
    {
        return Payment::query()
            ->with(['invoice', 'customer'])
            ->whereBetween('paid_at', $this->dateRange())
            ->when($this->clientId, fn (Builder $query) => $query->where('customer_id', $this->clientId))
            ->orderBy('paid_at');
    }

    public function summaryLine(): string
    {
        $query = $this->reportQuery();

        return trans('ip.report_summary_payments', [
            'count' => $query->count(),
            'total' => $this->money($query->sum('payment_amount')),
        ]);
    }

    protected function reportColumns(): array
    {
        return [
            TextColumn::make('paid_at')->label(trans('ip.payment_date'))->date(),
            TextColumn::make('payment_number')->label(trans('ip.payment_number')),
            TextColumn::make('payment_method')->label(trans('ip.payment_method')),
            TextColumn::make('invoice.invoice_number')->label(trans('ip.invoice_number')),
            TextColumn::make('customer.company_name')->label(trans('ip.client')),
            TextColumn::make('payment_amount')->label(trans('ip.amount'))->numeric(2)->alignRight(),
        ];
    }

    protected function csvHeaders(): array
    {
        return [
            trans('ip.payment_date'),
            trans('ip.payment_number'),
            trans('ip.payment_method'),
            trans('ip.invoice_number'),
            trans('ip.client'),
            trans('ip.amount'),
        ];
    }

    protected function csvRow($record): array
    {
        return [
            $record->paid_at?->toDateString(),
            $record->payment_number,
            $record->payment_method instanceof BackedEnum ? $record->payment_method->value : (string) $record->payment_method,
            $record->invoice?->invoice_number,
            $record->customer?->company_name,
            $this->money($record->payment_amount),
        ];
    }
}
