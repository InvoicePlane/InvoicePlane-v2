<?php

namespace Modules\Core\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Panel;

class Dashboard extends BaseDashboard
{
    protected static bool $isScopedToTenant = false;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'dashboard';
    }
}
