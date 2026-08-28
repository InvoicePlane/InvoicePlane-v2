<?php

namespace Modules\Core\Filament\Company\Pages\Reports;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Clients\Models\Relation;

class InvoicedByClientReport extends BaseTabularReportPage
{
    public static function getNavigationLabel(): string
    {
        return trans('ip.report_invoiced_by_client');
    }

    public function getTitle(): string
    {
        return trans('ip.report_invoiced_by_client');
    }

    public function reportQuery(): Builder
    {
        $range = fn (Builder $query) => $query->whereBetween('invoiced_at', $this->dateRange());

        return Relation::query()
            ->when($this->clientId, fn (Builder $query) => $query->whereKey($this->clientId))
            ->whereHas('invoices', $range)
            ->withCount(['invoices as invoices_count' => $range])
            ->withSum(['invoices as invoiced_total' => $range], 'invoice_total')
            ->orderByDesc('invoiced_total');
    }

    public function summaryLine(): string
    {
        $rows = $this->reportQuery()->get();

        return trans('ip.report_summary_invoiced_by_client', [
            'clients' => $rows->count(),
            'total'   => $this->money($rows->sum('invoiced_total')),
        ]);
    }

    protected function reportColumns(): array
    {
        return [
            TextColumn::make('company_name')->label(trans('ip.client')),
            TextColumn::make('invoices_count')->label(trans('ip.invoices'))->alignRight(),
            TextColumn::make('invoiced_total')->label(trans('ip.total'))->numeric(2)->alignRight(),
        ];
    }

    protected function csvHeaders(): array
    {
        return [
            trans('ip.client'),
            trans('ip.invoices'),
            trans('ip.total'),
        ];
    }

    protected function csvRow($record): array
    {
        return [
            $record->company_name,
            $record->invoices_count,
            $this->money($record->invoiced_total),
        ];
    }
}
