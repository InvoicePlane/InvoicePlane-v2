<?php

namespace Modules\Core\Filament\Company\Pages\Reports;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;

class InvoicingHistoryReport extends BaseTabularReportPage
{
    public static function getNavigationLabel(): string
    {
        return trans('ip.report_invoicing_history');
    }

    public function getTitle(): string
    {
        return trans('ip.report_invoicing_history');
    }

    public function reportQuery(): Builder
    {
        return Invoice::query()
            ->with('customer')
            ->whereBetween('invoiced_at', $this->dateRange())
            ->when($this->clientId, fn (Builder $query) => $query->where('customer_id', $this->clientId))
            ->orderBy('invoiced_at');
    }

    public function summaryLine(): string
    {
        $query = $this->reportQuery();
        $paid  = (clone $query)->where('invoice_status', InvoiceStatus::PAID->value)->sum('invoice_total');

        return trans('ip.report_summary_invoicing', [
            'count'  => $query->count(),
            'total'  => $this->money($query->sum('invoice_total')),
            'paid'   => $this->money($paid),
            'unpaid' => $this->money($query->sum('invoice_total') - $paid),
        ]);
    }

    protected function reportColumns(): array
    {
        return [
            TextColumn::make('invoice_number')->label(trans('ip.invoice_number')),
            TextColumn::make('invoiced_at')->label(trans('ip.invoice_date'))->date(),
            TextColumn::make('invoice_status')->label(trans('ip.invoice_status'))->badge(),
            TextColumn::make('customer.company_name')->label(trans('ip.client')),
            TextColumn::make('invoice_total')->label(trans('ip.total'))->numeric(2)->alignRight(),
        ];
    }

    protected function csvHeaders(): array
    {
        return ['Invoice number', 'Date', 'Status', 'Client', 'Total'];
    }

    protected function csvRow($record): array
    {
        return [
            $record->invoice_number,
            $record->invoiced_at?->toDateString(),
            $record->invoice_status?->value,
            $record->customer?->company_name,
            $this->money($record->invoice_total),
        ];
    }
}
