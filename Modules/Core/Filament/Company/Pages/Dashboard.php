<?php

namespace Modules\Core\Filament\Company\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Modules\Core\Filament\Company\Widgets\CompanyStatsOverviewWidget;
use Modules\Invoices\Filament\Company\Widgets\RecentInvoicesWidget;
use Modules\Quotes\Filament\Company\Widgets\RecentQuotesWidget;

class Dashboard extends Page
{
    public static function getSlug(?Panel $panel = null): string
    {
        return 'dashboard';
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    public function getHeaderWidgets(): array
    {
        return [
            CompanyStatsOverviewWidget::class,
            RecentInvoicesWidget::class,
            RecentQuotesWidget::class,
            //RecentProjectsWidget::class,
            //RecentTasksWidget::class,
            //RecentExpensesWidget::class,
            //RecentPaymentsWidget::class,
        ];
    }
}
