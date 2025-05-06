<?php

namespace Modules\Core\Widgets\Dashboard\QuoteSummary\Composers;

class QuoteSummarySettingComposer
{
    public function compose($view): void
    {
        $view->with('dashboardTotalOptions', periods());
    }
}
