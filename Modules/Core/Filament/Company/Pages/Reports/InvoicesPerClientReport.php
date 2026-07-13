<?php

namespace Modules\Core\Filament\Company\Pages\Reports;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Clients\Models\Relation;

class InvoicesPerClientReport extends BaseTabularReportPage
{
    public static function getNavigationLabel(): string
    {
        return trans('ip.report_invoices_per_client');
    }

    public function getTitle(): string
    {
        return trans('ip.report_invoices_per_client');
    }

    public function reportQuery(): Builder
    {
        $range = fn (Builder $query) => $query->whereBetween('invoiced_at', $this->dateRange());

        return Relation::query()
            ->when($this->clientId, fn (Builder $query) => $query->whereKey($this->clientId))
            ->whereHas('invoices', $range)
            ->withCount(['invoices as invoices_count' => $range])
            ->withAvg(['invoices as average_value' => $range], 'invoice_total')
            ->orderByDesc('invoices_count');
    }

    public function summaryLine(): string
    {
        $rows = $this->reportQuery()->get();

        return trans('ip.report_summary_invoices_per_client', [
            'clients'  => $rows->count(),
            'invoices' => (int) $rows->sum('invoices_count'),
        ]);
    }

    protected function reportColumns(): array
    {
        return [
            TextColumn::make('company_name')->label(trans('ip.client')),
            TextColumn::make('invoices_count')->label(trans('ip.invoices'))->alignRight(),
            TextColumn::make('average_value')->label(trans('ip.average_value'))->numeric(2)->alignRight(),
        ];
    }

    protected function csvHeaders(): array
    {
        return ['Client', 'Invoices', 'Average value'];
    }

    protected function csvRow($record): array
    {
        return [
            $record->company_name,
            $record->invoices_count,
            $this->money($record->average_value),
        ];
    }
}
