<?php

namespace Modules\Expenses\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
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
    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['category_name' => 'Travel']
     */
    #[Group('crud')]
    public function it_lists_expense_categories(): void
    {
        /* Arrange */
        $payload = [
            'category_name' => 'Travel',
        ];

        $record = ExpenseCategory::factory()
            ->for($this->company)
            ->create($payload);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
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
        /* Arrange */
        $payload = [
            'category_name' => 'Meals',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
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
        /* Arrange */
        $payload = ['category_name' => null];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['category_name']);
        $this->assertDatabaseMissing('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_expense_category_through_a_modal(): void
    {
        /* Arrange */
        $record  = ExpenseCategory::factory()->for($this->company)->create(['category_name' => 'Original']);
        $payload = ['category_name' => 'Updated Name'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class)
            ->mountAction(TestAction::make('edit')->table($record), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
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
        /* Arrange */
        $payload = [
            'category_name' => 'Meals',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateExpenseCategory::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
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
        /* Arrange */
        $payload = ['category_name' => null];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateExpenseCategory::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['category_name']);
        $this->assertDatabaseMissing('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_expense_category(): void
    {
        /* Arrange */
        $record  = ExpenseCategory::factory()->for($this->company)->create(['category_name' => 'Original']);
        $payload = ['category_name' => 'Updated Name'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditExpenseCategory::class, ['record' => $record->id])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_expense_category(): void
    {
        /* Arrange */
        $expenseCategory = ExpenseCategory::factory()->for($this->company)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenseCategories::class)
            ->mountAction(TestAction::make('delete')->table($expenseCategory))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('expense_categories', ['id' => $expenseCategory->id]);
    }

    #[Test]
    #[Group('crud')]
    #[Group('slow')]
    public function it_confirms_deleted_category_is_no_longer_findable(): void
    {
        /* Arrange */
        $expenseCategory = ExpenseCategory::factory()->for($this->company)->create();
        $id              = $expenseCategory->id;

        /* Act */
        $expenseCategory->delete();

        /* Assert — hard delete: record is gone from DB and cannot be retrieved */
        $this->assertDatabaseMissing('expense_categories', ['id' => $id]);
        $this->assertNull(ExpenseCategory::find($id));
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
