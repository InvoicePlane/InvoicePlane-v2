<?php

namespace App\Widgets\Dashboard\InvoiceSummary\Composers;

class InvoiceSummarySettingComposer
{
    public function compose($view)
    {
        $view->with('dashboardTotalOptions', periods());
    }
}
