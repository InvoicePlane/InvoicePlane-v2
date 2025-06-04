<?php

namespace Modules\Core\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static bool $isScopedToTenant = false;

    public static function getSlug(): string
    {
        return 'dashboard';
    }
}
