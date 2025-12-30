<?php

namespace Modules\Expenses\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\CreateExpense;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\EditExpense;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\ListExpenses;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListExpenses::class)]
class ExpensesTest extends AbstractCompanyPanelTestCase
{
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
        $category = ExpenseCategory::factory()->for($this->company)->create();
        $customer = Relation::factory()->for($this->company)->customer()->create();

        $payload = [
            'expense_amount' => 550.00,
            'expensed_at'    => Carbon::parse('2024-12-01'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::FIXED,
            'expense_status' => ExpenseStatus::APPROVED,
        ];

        Expense::factory()->for($this->company)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('expenses', $payload);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_an_expense_through_a_modal(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'    => $customer->id,
            'category_id'    => $category->id,
            'expense_type'   => ExpenseType::FIXED->value,
            'expense_status' => ExpenseStatus::DRAFT->value,
            'expense_number' => 'EXP-001',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'description'    => 'Office chairs',
            'expenseItems'   => [
                [
                    'item_id'  => $product->id,
                    'quantity' => 2,
                    'price'    => 60,
                    'discount' => 0,
                    'subtotal' => 120,
                ],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('expenses', [
            'customer_id' => $customer->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_through_a_modal_without_required_expense_number(): void
    {
        /* arrange */
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['expense_number' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_through_a_modal_without_required_expensed_at(): void
    {
        /* arrange */
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['expensed_at' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_through_a_modal_without_required_amount(): void
    {
        /* arrange */
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['expense_amount' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_through_a_modal_without_required_category_id(): void
    {
        /* arrange */
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['category_id' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_through_a_modal_without_required_customer(): void
    {
        /* arrange */
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'expense_type'   => ExpenseType::ONE_TIME->value,
            'expense_status' => ExpenseStatus::APPROVED->value,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['customer_id' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_through_a_modal_without_required_type(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'    => $customer->id,
            'category_id'    => $category->id,
            'expense_status' => ExpenseStatus::DRAFT->value,
            'expense_number' => 'EXP-002',
            'expense_amount' => 50.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'description'    => 'Pens',
            'expenseItems'   => [
                [
                    'item_id'  => $product->id,
                    'quantity' => 1,
                    'price'    => 50,
                    'discount' => 0,
                    'subtotal' => 50,
                ],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component
            ->assertHasFormErrors(['expense_type' => 'required']);

        $this->assertDatabaseMissing('expenses', Arr::except($payload, ['expenseItems']));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_expense_through_a_modal_without_required_status(): void
    {
        /* arrange */
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['expense_status' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_expense_through_a_modal(): void
    {
        /* arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $category = ExpenseCategory::factory()->for($this->company)->create();

        $expense = Expense::factory()->for($this->company)->create([
            'customer_id'    => $customer->id,
            'category_id'    => $category->id,
            'expense_type'   => ExpenseType::FIXED->value,
            'expense_status' => ExpenseStatus::DRAFT->value,
        ]);

        $payload = ['expense_type' => ExpenseType::RECURRING];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class, ['record' => $expense->id])
            ->mountAction(TestAction::make('edit')->table($expense), $payload)
            ->fillForm($payload)
            ->callMountedAction();

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
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_an_expense(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'    => $customer->id,
            'category_id'    => $category->id,
            'expense_type'   => ExpenseType::FIXED->value,
            'expense_status' => ExpenseStatus::DRAFT->value,
            'expense_number' => 'EXP-001',
            'expense_amount' => 120.0000,
            'expensed_at'    => now()->format('Y-m-d'),
            'description'    => 'Office chairs',
            'expenseItems'   => [
                [
                    'item_id'  => $product->id,
                    'quantity' => 2,
                    'price'    => 60,
                    'discount' => 0,
                    'subtotal' => 120,
                ],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('expenses', Arr::except($payload, ['expenseItems']));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_without_required_expense_number(): void
    {
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_status' => ExpenseStatus::APPROVED,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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
        $category        = ExpenseCategory::factory()->for($this->company)->create();
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'expense_number' => 'EXP-4585487',
            'expense_amount' => 120.00,
            'expensed_at'    => now()->format('Y-m-d'),
            'category_id'    => $category->id,
            'customer_id'    => $customer->id,
            'expense_type'   => ExpenseType::ONE_TIME,
            'expenseItems'   => [
                [
                    'item_id'      => $product->id,
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
        /* arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $category = ExpenseCategory::factory()->for($this->company)->create();

        $expense = Expense::factory()->for($this->company)->create([
            'customer_id'    => $customer->id,
            'category_id'    => $category->id,
            'expense_type'   => ExpenseType::FIXED->value,
            'expense_status' => ExpenseStatus::REIMBURSED->value,
        ]);

        $payload = [
            'expense_status' => ExpenseStatus::DRAFT->value,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditExpense::class, ['record' => $expense->id])
            ->fillForm($payload)
            ->call('save');

        /* assert */
        $component
            ->assertSuccessful();

        $this->assertDatabaseHas('expenses', [
            'id'             => $expense->id,
            'expense_status' => ExpenseStatus::DRAFT->value,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_expense(): void
    {
        /* arrange */
        $expense = Expense::factory()->for($this->company)->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction(TestAction::make('delete')->table($expense))
            ->callMountedAction();

        /* assert */
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_expense_twice(): void
    {
        $this->markTestIncomplete('record to deleteAction cannot be null');

        /* arrange */
        $expense = Expense::factory()->for($this->company)->create();
        $expense->delete();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction(TestAction::make('delete')->table($expense))
            ->callMountedAction();

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
