<?php

namespace Modules\Core\Filament\Company\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Core\Support\NumberFormatter;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Models\Quote;

class CompanyStatsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // 1. Paid revenue
        $paidInvoicesQuery = Invoice::query()->where('invoice_status', InvoiceStatus::PAID);
        $paidCount         = $paidInvoicesQuery->count();
        $paidTotal         = (float) $paidInvoicesQuery->sum('invoice_total');

        // 2. Pending / awaiting payment
        $pendingQuery = Invoice::query()->whereIn('invoice_status', [
            InvoiceStatus::SENT,
            InvoiceStatus::VIEWED,
            InvoiceStatus::PARTIALLY_PAID,
        ]);
        $pendingCount = $pendingQuery->count();
        $pendingTotal = (float) $pendingQuery->sum('invoice_total');

        // 3. Overdue invoices
        $overdueQuery = Invoice::query()->where(function ($query) {
            $query->where('invoice_status', InvoiceStatus::OVERDUE)
                ->orWhere(function ($sub) {
                    $sub->whereNotIn('invoice_status', [InvoiceStatus::PAID, InvoiceStatus::DRAFT])
                        ->whereNotNull('invoice_due_at')
                        ->where('invoice_due_at', '<', now()->startOfDay());
                });
        });
        $overdueCount = $overdueQuery->count();
        $overdueTotal = (float) $overdueQuery->sum('invoice_total');

        // 4. Quotes pipeline
        $quotesQuery = Quote::query()->whereIn('quote_status', [
            QuoteStatus::DRAFT,
            QuoteStatus::SENT,
            QuoteStatus::VIEWED,
        ]);
        $quotesCount = $quotesQuery->count();
        $quotesTotal = (float) $quotesQuery->sum('quote_total');

        $invoicesUrl = InvoiceResource::getUrl('index');
        $quotesUrl   = QuoteResource::getUrl('index');

        return [
            Stat::make(trans('ip.invoice_status_paid'), NumberFormatter::formatCurrency($paidTotal))
                ->description(trans('ip.paid_invoices_count', ['count' => $paidCount]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url($invoicesUrl),

            Stat::make(trans('ip.awaiting_payment'), NumberFormatter::formatCurrency($pendingTotal))
                ->description(trans('ip.pending_invoices_count', ['count' => $pendingCount]))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url($invoicesUrl),

            Stat::make(trans('ip.invoice_status_overdue'), NumberFormatter::formatCurrency($overdueTotal))
                ->description(trans('ip.overdue_invoices_count', ['count' => $overdueCount]))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueCount > 0 ? 'danger' : 'gray')
                ->url($invoicesUrl),

            Stat::make(trans('ip.quotes_pipeline'), NumberFormatter::formatCurrency($quotesTotal))
                ->description(trans('ip.active_quotes_count', ['count' => $quotesCount]))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->url($quotesUrl),
        ];
    }
}
