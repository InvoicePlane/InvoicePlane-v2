<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Filament\Company\Widgets\RecentExpensesWidget;
use Modules\Payments\Filament\Company\Widgets\RecentPaymentsWidget;
use Modules\Projects\Filament\Company\Widgets\RecentProjectsWidget;
use Modules\Projects\Filament\Company\Widgets\RecentTasksWidget;
use PHPUnit\Framework\Attributes\Test;

class WidgetLocalizationTest extends AbstractTestCase
{
    #[Test]
    public function actual_widget_tables_use_the_translated_headings(): void
    {
        app('translator')->addLines(['ui.recent_expenses' => 'Ausgaben Test', 'ip.recent_payments' => 'Zahlungen Test', 'ui.recent_projects' => 'Projekte Test', 'ui.recent_tasks' => 'Aufgaben Test'], 'de');
        app()->setLocale('de');
        foreach ([RecentExpensesWidget::class => 'Ausgaben Test', RecentPaymentsWidget::class => 'Zahlungen Test', RecentProjectsWidget::class => 'Projekte Test', RecentTasksWidget::class => 'Aufgaben Test'] as $class => $expected) {
            $widget = new $class();
            $widget->bootedInteractsWithTable();
            $this->assertSame($expected, $widget->getTable()->getHeading());
        }
    }
}
