<?php

namespace Modules\Core\Filament\Company\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends Page
{
    public static function getSlug(?Panel $panel = null): string
    {
        return 'dashboard';
    }

    public function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
            FilamentInfoWidget::class,
        ];
    }
}
