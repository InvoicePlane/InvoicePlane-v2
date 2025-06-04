<?php

namespace Modules\Core\Filament\Company\Pages;

use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends Page
{
    public static function getSlug(): string
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
