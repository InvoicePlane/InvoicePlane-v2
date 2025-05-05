<?php

namespace Modules\Expenses\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\CreateExpense;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\EditExpense;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\ListExpenses;
use Modules\Expenses\Models\Expense;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExpenseResource::class)]
class ExpensesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * \Modules\Expenses\Filament\Company\Resources\ExpenseResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "vendor_id": "Value",
     * "customer_id": "Value",
     * "category_id": "Value",
     * "expense_number": "Example",
     * "expense_status": "Value",
     * "expense_type": "Value",
     * "expense_amount": "9.99",
     * "description": "Example"
     * }
     */
    public function it_creates_a_expense(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'     => 'Value',
            'vendor_id'      => 'Value',
            'customer_id'    => 'Value',
            'category_id'    => 'Value',
            'expense_number' => 'Example',
            'expense_status' => 'Value',
            'expense_type'   => 'Value',
            'expense_amount' => 9.99,
            'description'    => 'Example',
        ];

        Livewire::test(CreateExpense::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Expenses\Filament\Company\Resources\ExpenseResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "vendor_id": "Value",
     * "customer_id": "Value",
     * "category_id": "Value",
     * "expense_number": "Example",
     * "expense_status": "Value",
     * "expense_type": "Value",
     * "expense_amount": "9.99",
     * "description": "Example"
     * }
     */
    public function it_updates_a_expense(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Expense::factory()->create();

        $payload = [
            'company_id'     => 'Value',
            'vendor_id'      => 'Value',
            'customer_id'    => 'Value',
            'category_id'    => 'Value',
            'expense_number' => 'Example',
            'expense_status' => 'Value',
            'expense_type'   => 'Value',
            'expense_amount' => 9.99,
            'description'    => 'Example',
        ];

        Livewire::test(EditExpense::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Expenses\Filament\Company\Resources\ExpenseResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "vendor_id": "Value",
     * "customer_id": "Value",
     * "category_id": "Value",
     * "expense_number": "Example",
     * "expense_status": "Value",
     * "expense_type": "Value",
     * "expense_amount": "9.99",
     * "description": "Example"
     * }
     */
    public function it_deletes_an_expense(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Expense::factory()->create();

        Livewire::test(ListExpenses::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('expenses', ['id' => $record->id]);
    }

    // endregion

    // region usp
    // endregion
}
