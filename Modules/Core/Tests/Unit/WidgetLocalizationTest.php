<?php

namespace Modules\Core\Tests\Unit;

use Filament\Facades\Filament;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Filament\Company\Widgets\RecentExpensesWidget;
use Modules\Invoices\Filament\Company\Widgets\RecentInvoicesWidget;
use Modules\Payments\Filament\Company\Widgets\RecentPaymentsWidget;
use Modules\Projects\Filament\Company\Widgets\RecentProjectsWidget;
use Modules\Projects\Filament\Company\Widgets\RecentTasksWidget;
use Modules\Quotes\Filament\Company\Widgets\RecentQuotesWidget;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('smoke')]
class WidgetLocalizationTest extends AbstractTestCase
{
    public static function widgets(): array
    {
        return [
            'quotes'   => [RecentQuotesWidget::class, 'ip.recent_quotes', 'ip.quote', 'ip.quotes'],
            'invoices' => [RecentInvoicesWidget::class, 'ip.recent_invoices', 'ip.invoice', 'ip.invoices'],
            'expenses' => [RecentExpensesWidget::class, 'ui.recent_expenses', 'ip.expense', 'ip.expenses'],
            'payments' => [RecentPaymentsWidget::class, 'ip.recent_payments', 'ip.payment', 'ip.payments'],
            'projects' => [RecentProjectsWidget::class, 'ui.recent_projects', 'ip.project', 'ip.projects'],
            'tasks'    => [RecentTasksWidget::class, 'ui.recent_tasks', 'ip.task', 'ip.tasks'],
        ];
    }

    #[Test]
    #[DataProvider('widgets')]
    public function it_uses_translated_headings_and_empty_state_model_labels(string $class, string $heading, string $singular, string $plural): void
    {
        Filament::setCurrentPanel(Filament::getPanel('company'));
        Filament::setTenant(new Company(['id' => 1, 'search_code' => 'test']), isQuiet: true);
        foreach (['de', 'en'] as $locale) {
            app('translator')->addLines([$heading => "{$locale} heading", $singular => "{$locale} record", $plural => "{$locale} records"], $locale);
            app()->setLocale($locale);
            $widget = new $class();
            $widget->bootedInteractsWithTable();
            $table = $widget->getTable();
            $this->assertSame("{$locale} heading", $table->getHeading());
            $this->assertSame("{$locale} record", $table->getModelLabel());
            $this->assertSame("{$locale} records", $table->getPluralModelLabel());
            $this->assertStringContainsString("{$locale} records", $table->getEmptyStateHeading());
        }
    }
}
