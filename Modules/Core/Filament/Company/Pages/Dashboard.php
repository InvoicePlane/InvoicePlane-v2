<?php

namespace Modules\Core\Filament\Company\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Modules\Invoices\Filament\Company\Widgets\RecentInvoicesWidget;
use Modules\Quotes\Filament\Company\Widgets\RecentQuotesWidget;

class Dashboard extends Page
{
    public static function getSlug(?Panel $panel = null): string
    {
        return 'dashboard';
    }

    public function getHeaderWidgets(): array
    {
        return [
            //RecentQuotesWidget::class,
            //RecentInvoicesWidget::class,
            //RecentProjectsWidget::class,
            //RecentTasksWidget::class,
            //RecentExpensesWidget::class,
            //RecentPaymentsWidget::class,
        ];
    }
}
