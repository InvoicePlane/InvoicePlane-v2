<?php

namespace Modules\Expenses\Tests\Feature;

use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\CreateExpense;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\EditExpense;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\ListExpenses;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Products\Models\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExpenseResource::class)]
class ExpensesTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    # region smoke
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
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_an_expense_with_items(): void
    {
        $this->markTestIncomplete();

        $company  = $this->user->companies()->first();
        $category = ExpenseCategory::factory()->for($company)->create();
        $customer = Relation::factory()->for($company)->customer()->create();
        $item     = Product::factory()->for($company)->create();

        $payload = [
            'customer_id'    => $customer->id,
            'expense_number' => 'EXP-4585487',
            'expense_status' => ExpenseStatus::COMPLETED,
            'category_id'    => $category->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'expenseItems'   => [
                [
                    'item_id'      => $item->id,
                    'quantity'     => 2,
                    'price'        => 10,
                    'discount'     => 0,
                    'subtotal'     => 20,
                    'is_recurring' => false,
                    'tax_1'        => 2,
                    'tax_2'        => 1,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create') // Mount the modal for CreateAction
            ->fillForm($payload)    // Fill the form including expenseItems
            ->callMountedAction();  // Submit the modal action

        $component->assertHasNoFormErrors(); // Check for validation errors

        $this->assertDatabaseHas('expenses', [
            'expense_number' => $payload['expense_number'],
            'expense_amount' => $payload['expense_amount'],
        ]);

        $this->assertDatabaseHas('expense_items', [
            'item_id'  => $payload['expenseItems'][0]['item_id'],
            'quantity' => $payload['expenseItems'][0]['quantity'],
            'price'    => $payload['expenseItems'][0]['price'],
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_expense_number(): void
    {
        $company  = $this->user->companies()->first();
        $category = ExpenseCategory::factory()->for($company)->create();
        $customer = Relation::factory()->for($company)->customer()->create();
        $item     = Product::factory()->for($company)->create();

        $payload = [
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::COMPLETED,
            'expenseItems'   => [
                [
                    'item_id'      => $item->id,
                    'quantity'     => 2,
                    'price'        => 10,
                    'discount'     => 0,
                    'subtotal'     => 20,
                    'is_recurring' => false,
                    'tax_1'        => 2,
                    'tax_2'        => 1,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            //->fillForm($payload)
            ->callAction('create', data: $payload);

        /* assert */
        $component->assertHasFormErrors(['expense_number' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_without_required_expensed_at(): void
    {
        $company  = $this->user->companies()->first();
        $category = ExpenseCategory::factory()->for($company)->create();
        $customer = Relation::factory()->for($company)->customer()->create();
        $item     = Product::factory()->for($company)->create();

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::COMPLETED,
            'expenseItems'   => [
                [
                    'item_id'      => $item->id,
                    'quantity'     => 2,
                    'price'        => 10,
                    'discount'     => 0,
                    'subtotal'     => 20,
                    'is_recurring' => false,
                    'tax_1'        => 2,
                    'tax_2'        => 1,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['expensed_at' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_without_required_amount(): void
    {
        $company  = $this->user->companies()->first();
        $category = ExpenseCategory::factory()->for($company)->create();
        $customer = Relation::factory()->for($company)->customer()->create();
        $item     = Product::factory()->for($company)->create();

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::COMPLETED,
            'expenseItems'   => [
                [
                    'item_id'      => $item->id,
                    'quantity'     => 2,
                    'price'        => 10,
                    'discount'     => 0,
                    'subtotal'     => 20,
                    'is_recurring' => false,
                    'tax_1'        => 2,
                    'tax_2'        => 1,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['expense_amount' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_without_required_category_id(): void
    {
        $company  = $this->user->companies()->first();
        $category = ExpenseCategory::factory()->for($company)->create();
        $customer = Relation::factory()->for($company)->customer()->create();
        $item     = Product::factory()->for($company)->create();

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::COMPLETED,
            'expenseItems'   => [
                [
                    'item_id'      => $item->id,
                    'quantity'     => 2,
                    'price'        => 10,
                    'discount'     => 0,
                    'subtotal'     => 20,
                    'is_recurring' => false,
                    'tax_1'        => 2,
                    'tax_2'        => 1,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['category_id' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_without_required_customer_id(): void
    {
        $company  = $this->user->companies()->first();
        $category = ExpenseCategory::factory()->for($company)->create();
        $customer = Relation::factory()->for($company)->customer()->create();
        $item     = Product::factory()->for($company)->create();

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::COMPLETED,
            'expenseItems'   => [
                [
                    'item_id'      => $item->id,
                    'quantity'     => 2,
                    'price'        => 10,
                    'discount'     => 0,
                    'subtotal'     => 20,
                    'is_recurring' => false,
                    'tax_1'        => 2,
                    'tax_2'        => 1,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['customer_id' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_without_required_expense_type(): void
    {
        $company  = $this->user->companies()->first();
        $category = ExpenseCategory::factory()->for($company)->create();
        $customer = Relation::factory()->for($company)->customer()->create();
        $item     = Product::factory()->for($company)->create();

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_status' => ExpenseStatus::COMPLETED,
            'expenseItems'   => [
                [
                    'item_id'      => $item->id,
                    'quantity'     => 2,
                    'price'        => 10,
                    'discount'     => 0,
                    'subtotal'     => 20,
                    'is_recurring' => false,
                    'tax_1'        => 2,
                    'tax_2'        => 1,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['expense_type' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_without_required_expense_status(): void
    {
        $company  = $this->user->companies()->first();
        $category = ExpenseCategory::factory()->for($company)->create();
        $customer = Relation::factory()->for($company)->customer()->create();
        $item     = Product::factory()->for($company)->create();

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expenseItems'   => [
                [
                    'item_id'      => $item->id,
                    'quantity'     => 2,
                    'price'        => 10,
                    'discount'     => 0,
                    'subtotal'     => 20,
                    'is_recurring' => false,
                    'tax_1'        => 2,
                    'tax_2'        => 1,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['expense_status' => 'required']);
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

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditExpense::class, ['record' => $expense->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
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

        /* act */
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

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListExpenses::class)->callTableAction('delete', $record);

        /* assert */
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

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListExpenses::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('expenses', ['id' => $record->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_expenses_of_another_tenant(): void
    {
        $this->markTestIncomplete();

        // Arrange: create an expense for a different company
        $otherUser    = User::factory()->create();
        $otherCompany = $otherUser->companies()->first();
        $expense      = Expense::factory()->for($otherCompany)->create();

        // Act: try to access as this user (should be forbidden or 404)
        $component = Livewire::actingAs($this->user)
            ->test(EditExpense::class, ['record' => $expense->id]);

        // Assert: should not be able to access (forbidden or not found)
        $component->assertForbidden();
    }
    # endregion
}
