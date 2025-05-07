<?php

namespace Modules\Core\Widgets\Dashboard\QuoteSummary\Composers;

use Modules\Core\Support\Results\Quotes;

use Modules\Core\Support\CurrencyFormatter;

use Modules\Core\Filament\Company\Pages\Dashboard;

use Modules\Core\Widgets\Dashboard\QuoteSummary\Composers\QuoteSummaryWidgetComposer;

use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrencyFormatter;
use Modules\Quotes\Models\QuoteAmount;

class QuoteSummaryWidgetComposer
{
    public function compose($view): void
    {
        $view->with('quotesTotalDraft', $this->getQuoteTotalDraft())
            ->with('quotesTotalSent', $this->getQuoteTotalSent())
            ->with('quotesTotalApproved', $this->getQuoteTotalApproved())
            ->with('quotesTotalRejected', $this->getQuoteTotalRejected())
            ->with('quoteDashboardTotalOptions', periods());
    }

    private function getQuoteTotalDraft()
    {
        return CurrencyFormatter::format(QuoteAmount::join('quotes', 'quotes.id', '=', 'quote_amounts.quote_id')
            ->whereHas('quote', function ($q): void {
                $q->draft();
                $q->where('invoice_id', 0);
                switch (config('ip.widgetQuoteSummaryDashboardTotals')) {
                    case 'year_to_date':
                        $q->yearToDate();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('ip.widgetQuoteSummaryDashboardTotalsFromDate'), config('ip.widgetQuoteSummaryDashboardTotalsToDate'));
                        break;
                }
            })->sum(DB::raw('total / exchange_rate')));
    }

    private function getQuoteTotalSent()
    {
        return CurrencyFormatter::format(QuoteAmount::join('quotes', 'quotes.id', '=', 'quote_amounts.quote_id')
            ->whereHas('quote', function ($q): void {
                $q->sent();
                $q->where('invoice_id', 0);
                switch (config('ip.widgetQuoteSummaryDashboardTotals')) {
                    case 'year_to_date':
                        $q->yearToDate();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('ip.widgetQuoteSummaryDashboardTotalsFromDate'), config('ip.widgetQuoteSummaryDashboardTotalsToDate'));
                        break;
                }
            })->sum(DB::raw('total / exchange_rate')));
    }

    private function getQuoteTotalApproved()
    {
        return CurrencyFormatter::format(QuoteAmount::join('quotes', 'quotes.id', '=', 'quote_amounts.quote_id')
            ->whereHas('quote', function ($q): void {
                $q->approved();
                $q->where('invoice_id', 0);
                switch (config('ip.widgetQuoteSummaryDashboardTotals')) {
                    case 'year_to_date':
                        $q->yearToDate();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('ip.widgetQuoteSummaryDashboardTotalsFromDate'), config('ip.widgetQuoteSummaryDashboardTotalsToDate'));
                        break;
                }
            })->sum(DB::raw('total / exchange_rate')));
    }

    private function getQuoteTotalRejected()
    {
        return CurrencyFormatter::format(QuoteAmount::join('quotes', 'quotes.id', '=', 'quote_amounts.quote_id')
            ->whereHas('quote', function ($q): void {
                $q->rejected();
                $q->where('invoice_id', 0);
                switch (config('ip.widgetQuoteSummaryDashboardTotals')) {
                    case 'year_to_date':
                        $q->yearToDate();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('ip.widgetQuoteSummaryDashboardTotalsFromDate'), config('ip.widgetQuoteSummaryDashboardTotalsToDate'));
                        break;
                }
            })->sum(DB::raw('total / exchange_rate')));
    }
}
