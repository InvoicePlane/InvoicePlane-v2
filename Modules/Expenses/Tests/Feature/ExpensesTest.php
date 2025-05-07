<?php

namespace Modules\Expenses\Tests\Feature;

use Modules\Expenses\Enums\ExpenseType;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Expenses\Models\ExpenseCategory;

use Modules\Expenses\Models\Expense;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\EditExpense;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource;

use Modules\Core\Support\Results\Expenses;

use Modules\Expenses\Tests\Feature\ExpensesTest;

use Modules\Core\Support\Results\Clients;

use Modules\Expenses\Enums\ExpenseStatus;

use Modules\Core\Models\Company;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\CreateExpense;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\ListExpenses;

use Modules\Clients\Models\Relation;

use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExpenseResource::class)]
class ExpensesTest extends AbstractTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['amount' => 550.00, 'expensed_at' => '2024-12-01', 'expense_type' => 'fixed']
     */
    public function it_lists_expenses(): void
    {
        // arrange
        $vendor   = ExpenseVendor::factory()->for($this->user->company)->create();
        $category = ExpenseCategory::factory()->for($this->user->company)->create();
        $customer = Relation::factory()->for($this->user->company)->customer()->create();

        $record = Expense::factory()->for($this->user->company)->create([
            'amount'       => 550.00,
            'expensed_at'  => Carbon::parse('2024-12-01'),
            'vendor_id'    => $vendor->id,
            'category_id'  => $category->id,
            'customer_id'  => $customer->id,
            'expense_type' => ExpenseType::FIXED,
            'status'       => ExpenseStatus::ACTIVE,
        ]);

        // act + assert
        Livewire::test(ListExpenses::class)
            ->actingAs($this->user)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload [
     *   'amount' => 120.00,
     *   'expensed_at' => '2024-11-01',
     *   'vendor_id' => 1,
     *   'category_id' => 1,
     *   'customer_id' => 1,
     *   'expense_type' => 'fixed',
     *   'status' => 'active'
     * ]
     */
    public function it_creates_an_expense(): void
    {
        // arrange
        $vendor   = ExpenseVendor::factory()->for($this->user->company)->create();
        $category = ExpenseCategory::factory()->for($this->user->company)->create();
        $customer = Relation::factory()->for($this->user->company)->customer()->create();

        $payload = [
            'amount'       => 120.00,
            'expensed_at'  => '2024-11-01',
            'vendor_id'    => $vendor->id,
            'category_id'  => $category->id,
            'customer_id'  => $customer->id,
            'expense_type' => ExpenseType::FIXED,
            'status'       => ExpenseStatus::ACTIVE,
        ];

        // act
        Livewire::test(CreateExpense::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expenses', [
            'amount'       => 120.00,
            'vendor_id'    => $vendor->id,
            'category_id'  => $category->id,
            'customer_id'  => $customer->id,
            'expense_type' => ExpenseType::FIXED,
            'status'       => ExpenseStatus::ACTIVE,
        ]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['amount' => null]
     */
    public function it_fails_to_create_expense_without_amount(): void
    {
        // arrange
        $payload = ['amount' => null];

        // act
        Livewire::test(CreateExpense::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['amount']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['expense_type' => 'recurring']
     */
    public function it_updates_an_expense(): void
    {
        // arrange
        $expense = Expense::factory()->for($this->user->company)->create([
            'expense_type' => ExpenseType::FIXED,
        ]);

        $payload = ['expense_type' => ExpenseType::RECURRING];

        // act
        Livewire::test(EditExpense::class, ['record' => $expense->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expenses', [
            'id'           => $expense->id,
            'expense_type' => ExpenseType::RECURRING,
        ]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['expense_type' => null]
     */
    public function it_fails_to_update_expense_with_empty_type(): void
    {
        // arrange
        $expense = Expense::factory()->for($this->user->company)->create();

        $payload = ['expense_type' => null];

        // act
        Livewire::test(EditExpense::class, ['record' => $expense->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors(['expense_type']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_an_expense(): void
    {
        // arrange
        $record = Expense::factory()->for($this->user->company)->create();

        // act
        Livewire::test(ListExpenses::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $record);

        // assert
        $this->assertDatabaseMissing('expenses', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_expense_twice(): void
    {
        // arrange
        $record = Expense::factory()->for($this->user->company)->create();
        $record->delete();

        // act + assert
        Livewire::test(ListExpenses::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $record)
            ->assertHasErrors();

        $this->assertDatabaseMissing('expenses', ['id' => $record->id]);
    }
}
