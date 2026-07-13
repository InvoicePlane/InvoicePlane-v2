<?php

namespace Modules\Core\Filament\Company\Pages\Reports;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;

class SalesByDateReport extends BaseTabularReportPage
{
    public static function getNavigationLabel(): string
    {
        return trans('ip.report_sales_by_date');
    }

    public function getTitle(): string
    {
        return trans('ip.report_sales_by_date');
    }

    public function reportQuery(): Builder
    {
        return Invoice::query()
            ->selectRaw('MIN(id) as id, invoiced_at, COUNT(*) as invoices_count, SUM(invoice_total) as daily_total')
            ->where('invoice_status', InvoiceStatus::PAID->value)
            ->whereBetween('invoiced_at', $this->dateRange())
            ->when($this->clientId, fn (Builder $query) => $query->where('customer_id', $this->clientId))
            ->groupBy('invoiced_at')
            ->orderBy('invoiced_at');
    }

    public function summaryLine(): string
    {
        $rows = $this->reportQuery()->get();

        return trans('ip.report_summary_sales_by_date', [
            'days'  => $rows->count(),
            'total' => $this->money($rows->sum('daily_total')),
        ]);
    }

    protected function reportColumns(): array
    {
        return [
            TextColumn::make('invoiced_at')->label(trans('ip.date'))->date(),
            TextColumn::make('invoices_count')->label(trans('ip.invoices'))->alignRight(),
            TextColumn::make('daily_total')->label(trans('ip.total'))->numeric(2)->alignRight(),
        ];
    }

    protected function csvHeaders(): array
    {
        return ['Date', 'Paid invoices', 'Revenue'];
    }

    protected function csvRow($record): array
    {
        return [
            $record->invoiced_at?->toDateString(),
            $record->invoices_count,
            $this->money($record->daily_total),
        ];
    }
}
