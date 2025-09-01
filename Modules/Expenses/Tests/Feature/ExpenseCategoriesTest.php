<?php

namespace Modules\Expenses\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Pages\CreateExpenseCategory;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Pages\EditExpenseCategory;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Pages\ListExpenseCategories;
use Modules\Expenses\Models\ExpenseCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListExpenseCategories::class)]
class ExpenseCategoriesTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['category_name' => 'Travel']
     */
    #[Group('crud')]
    public function it_lists_expense_categories(): void
    {
        /* arrange */
        $payload = [
            'category_name' => 'Travel',
        ];

        $record = ExpenseCategory::factory()
            ->for($this->user->companies()->first())
            ->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class, ['tenant' => Str::lower($this->user->companies()->first()->search_code)]);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas($record);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "category_name": "Travel"
     * }
     */
    public function it_creates_an_expense_category_through_a_modal(): void
    {
        /* arrange */
        $payload = [
            'category_name' => 'Meals',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: category_name
     * {}
     */
    public function it_fails_to_create_category_through_a_modal_without_required_category_name(): void
    {
        /* arrange */
        $payload = ['category_name' => null];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['category_name']);
        $this->assertDatabaseMissing('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_expense_category_through_a_modal(): void
    {
        /* arrange */
        $record  = ExpenseCategory::factory()->for($this->user->companies()->first())->create(['category_name' => 'Original']);
        $payload = ['category_name' => 'Updated Name'];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class, ['record' => $record->id])
            ->mountAction(TestAction::make('edit')->table($record), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('expense_categories', $payload);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "category_name": "Travel"
     * }
     */
    public function it_creates_an_expense_category(): void
    {
        /* arrange */
        $payload = [
            'category_name' => 'Meals',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateExpenseCategory::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: category_name
     * {}
     */
    public function it_fails_to_create_category_without_required_category_name(): void
    {
        /* arrange */
        $payload = ['category_name' => null];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateExpenseCategory::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['category_name']);
        $this->assertDatabaseMissing('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_expense_category(): void
    {
        /* arrange */
        $record  = ExpenseCategory::factory()->for($this->user->companies()->first())->create(['category_name' => 'Original']);
        $payload = ['category_name' => 'Updated Name'];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditExpenseCategory::class, ['record' => $record->id])
            ->fillForm($payload)
            ->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_expense_category(): void
    {
        /* arrange */
        $expenseCategory = ExpenseCategory::factory()->for($this->user->companies()->first())->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class)
            ->mountAction(TestAction::make('delete')->table($expenseCategory))
            ->callMountedAction();

        /* assert */
        $this->assertDatabaseMissing('expense_categories', ['id' => $expenseCategory->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_already_deleted_category(): void
    {
        $this->markTestIncomplete('record to deleteAction cannot be null');

        /* arrange */
        $expenseCategory = ExpenseCategory::factory()->for($this->user->companies()->first())->create();
        $expenseCategory->delete();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class)
            ->mountAction(TestAction::make('delete')->table($expenseCategory))
            ->callMountedAction();

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('expense_categories', ['id' => $expenseCategory->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
