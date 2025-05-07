<?php

namespace Modules\Core\Widgets\Dashboard\ClientActivity\Composers;

use Modules\Activity\Models\Activity;

class ClientActivityWidgetComposer
{
    public function compose($view): void
    {
        $recentClientActivity = Activity::where('activity', 'like', 'public%')
            ->orderBy('created_at', 'DESC')
            ->take(5)
            ->get();

        $view->with('recentClientActivity', $recentClientActivity);
    }
}
