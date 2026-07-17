<?php

namespace Modules\Expenses\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Expenses\Filament\Company\Widgets\RecentExpensesWidget;
use Modules\Expenses\Models\Expense;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RecentExpensesWidget::class)]
class RecentExpensesWidgetTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_links_each_row_to_the_expenses_index_page(): void
    {
        /* Arrange */
        Expense::factory()
            ->for($this->company)
            ->create(['expense_number' => 'EXP-0001']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(RecentExpensesWidget::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee(ExpenseResource::getUrl('index'), false);
    }
}
