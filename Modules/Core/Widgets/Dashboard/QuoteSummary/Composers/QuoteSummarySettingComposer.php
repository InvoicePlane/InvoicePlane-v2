<?php

namespace Modules\Core\Widgets\Dashboard\QuoteSummary\Composers;

use Modules\Core\Filament\Company\Pages\Dashboard;

use Modules\Core\Widgets\Dashboard\QuoteSummary\Composers\QuoteSummarySettingComposer;

class QuoteSummarySettingComposer
{
    public function compose($view): void
    {
        $view->with('dashboardTotalOptions', periods());
    }
}
