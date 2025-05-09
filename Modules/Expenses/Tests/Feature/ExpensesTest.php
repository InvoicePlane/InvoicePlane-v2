<?php

namespace Modules\Expenses\Tests\Feature;

use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\CreateExpense;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\EditExpense;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\ListExpenses;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Products\Models\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExpenseResource::class)]
class ExpensesTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['expense_amount' => 550.00, 'expensed_at' => '2024-12-01', 'expense_type' => 'fixed']
     */
    #[Group('crud')]
    public function it_lists_expenses(): void
    {
        /* arrange */
        $category = ExpenseCategory::factory()->for($this->user->companies()->first())->create();
        $customer = Relation::factory()->for($this->user->companies()->first())->customer()->create();

        $payload = [
            'expense_amount' => 550.00,
            'expensed_at'    => Carbon::parse('2024-12-01'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::FIXED,
            'expense_status' => ExpenseStatus::COMPLETED,
        ];

        Expense::factory()->for($this->user->companies()->first())->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('expenses', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_an_expense_with_items(): void
    {
        $this->markTestIncomplete('expense_id on expense items nullable?');
        /**
         * It's somehow _creating_ a taxRate.
         * company_id doesn't have a default value in Database/Factories/ProductFactory.php line 37.
         */

        /* arrange */
        $category = ExpenseCategory::factory()->for($this->user->companies()->first())->create();
        $customer = Relation::factory()->for($this->user->companies()->first())->customer()->create();
        $item     = Product::query()->inRandomOrder()->first() ?? Product::factory()->create();

        $payload = [
            'expense_amount' => 120.00,
            'expensed_at'    => Carbon::parse('2024-11-01'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::COMPLETED,
            'expense_items'  => [
                'item_id'      => $item->id,
                'is_recurring' => false,
                'quantity'     => 2,
                'price'        => 10,
            ],
        ];

        /** act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expenses', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_without_amount(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = ['expense_amount' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateExpense::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['expense_amount']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_expense(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $expense = Expense::factory()->for($this->user->companies()->first())->create([
            'expense_type' => ExpenseType::FIXED,
        ]);

        $payload = ['expense_type' => ExpenseType::RECURRING];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditExpense::class, ['record' => $expense->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expenses', [
            'id'           => $expense->id,
            'expense_type' => ExpenseType::RECURRING,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_expense_with_empty_type(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $expense = Expense::factory()->for($this->user->companies()->first())->create();

        $payload = ['expense_type' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditExpense::class, ['record' => $expense->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['expense_type']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_expense(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = Expense::factory()->for($this->user->companies()->first())->create();

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListExpenses::class)->callTableAction('delete', $record);

        // assert
        $this->assertDatabaseMissing('expenses', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_expense_twice(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = Expense::factory()->for($this->user->companies()->first())->create();
        $record->delete();

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListExpenses::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('expenses', ['id' => $record->id]);
    }
}
