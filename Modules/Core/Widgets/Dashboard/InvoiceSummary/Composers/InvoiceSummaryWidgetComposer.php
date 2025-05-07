<?php

namespace Modules\Core\Widgets\Dashboard\InvoiceSummary\Composers;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Models\Payment;

use Modules\Core\Support\CurrencyFormatter;

use Modules\Core\Filament\Company\Pages\Dashboard;

use Modules\Core\Widgets\Dashboard\InvoiceSummary\Composers\InvoiceSummaryWidgetComposer;

use Modules\Core\Support\Results\Invoices;

use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrencyFormatter;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Payments\Models\Payment;

class InvoiceSummaryWidgetComposer
{
    public function compose($view): void
    {
        $view->with('invoicesTotalDraft', $this->getInvoicesTotalDraft())
            ->with('invoicesTotalSent', $this->getInvoicesTotalSent())
            ->with('invoicesTotalPaid', $this->getInvoicesTotalPaid())
            ->with('invoicesTotalOverdue', $this->getInvoicesTotalOverdue())
            ->with('invoiceDashboardTotalOptions', periods());
    }

    private function getInvoicesTotalDraft()
    {
        return CurrencyFormatter::format(InvoiceAmount::join('invoices', 'invoices.id', '=', 'invoice_amounts.invoice_id')
            ->whereHas('invoice', function ($q): void {
                $q->draft();
                switch (config('ip.widgetInvoiceSummaryDashboardTotals')) {
                    case 'year_to_date':
                        $q->yearToDate();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('ip.widgetInvoiceSummaryDashboardTotalsFromDate'), config('ip.widgetInvoiceSummaryDashboardTotalsToDate'));
                        break;
                }
            })->sum(DB::raw('balance / exchange_rate')));
    }

    private function getInvoicesTotalSent()
    {
        return CurrencyFormatter::format(InvoiceAmount::join('invoices', 'invoices.id', '=', 'invoice_amounts.invoice_id')
            ->whereHas('invoice', function ($q): void {
                $q->sent();
                switch (config('ip.widgetInvoiceSummaryDashboardTotals')) {
                    case 'year_to_date':
                        $q->yearToDate();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('ip.widgetInvoiceSummaryDashboardTotalsFromDate'), config('ip.widgetInvoiceSummaryDashboardTotalsToDate'));
                        break;
                }
            })->sum(DB::raw('balance / exchange_rate')));
    }

    private function getInvoicesTotalPaid()
    {
        $payments = Payment::join('invoices', 'invoices.id', '=', 'payments.invoice_id');

        switch (config('ip.widgetInvoiceSummaryDashboardTotals')) {
            case 'year_to_date':
                $payments->yearToDate();
                break;
            case 'this_quarter':
                $payments->thisQuarter();
                break;
            case 'custom_date_range':
                $payments->dateRange(config('ip.widgetInvoiceSummaryDashboardTotalsFromDate'), config('ip.widgetInvoiceSummaryDashboardTotalsToDate'));
                break;
        }

        return CurrencyFormatter::format($payments->sum(DB::raw('amount / exchange_rate')));
    }

    private function getInvoicesTotalOverdue()
    {
        return CurrencyFormatter::format(InvoiceAmount::join('invoices', 'invoices.id', '=', 'invoice_amounts.invoice_id')
            ->whereHas('invoice', function ($q): void {
                $q->overdue();
                switch (config('ip.widgetInvoiceSummaryDashboardTotals')) {
                    case 'year_to_date':
                        $q->yearToDate();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('ip.widgetInvoiceSummaryDashboardTotalsFromDate'), config('ip.widgetInvoiceSummaryDashboardTotalsToDate'));
                        break;
                }
            })->sum(DB::raw('balance / exchange_rate')));
    }
}
