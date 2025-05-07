<?php

namespace Modules\Core\Widgets\Dashboard\InvoiceSummary\Controllers;

use App\Http\Controllers\Controller;
use Modules\Settings\Models\Setting;

class WidgetController extends Controller
{
    public function renderPartial()
    {
        Setting::saveByKey('widgetInvoiceSummaryDashboardTotals', request('widgetInvoiceSummaryDashboardTotals'));

        if (request()->has('widgetInvoiceSummaryDashboardTotalsFromDate')) {
            Setting::saveByKey('widgetInvoiceSummaryDashboardTotalsFromDate', request('widgetInvoiceSummaryDashboardTotalsFromDate'));
        }

        if (request()->has('widgetInvoiceSummaryDashboardTotalsToDate')) {
            Setting::saveByKey('widgetInvoiceSummaryDashboardTotalsToDate', request('widgetInvoiceSummaryDashboardTotalsToDate'));
        }

        Setting::setAll();

        return view('InvoiceSummaryWidget');
    }
}
