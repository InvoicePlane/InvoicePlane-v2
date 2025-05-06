<?php

namespace Modules\Core\Widgets\Dashboard\InvoiceSummary\Composers;

class InvoiceSummarySettingComposer
{
    public function compose($view): void
    {
        $view->with('dashboardTotalOptions', periods());
    }
}
