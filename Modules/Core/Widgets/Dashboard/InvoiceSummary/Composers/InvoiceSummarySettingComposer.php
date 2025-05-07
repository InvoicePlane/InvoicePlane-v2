<?php

namespace Modules\Core\Widgets\Dashboard\InvoiceSummary\Composers;

use Modules\Core\Widgets\Dashboard\InvoiceSummary\Composers\InvoiceSummarySettingComposer;

use Modules\Core\Filament\Company\Pages\Dashboard;

class InvoiceSummarySettingComposer
{
    public function compose($view): void
    {
        $view->with('dashboardTotalOptions', periods());
    }
}
