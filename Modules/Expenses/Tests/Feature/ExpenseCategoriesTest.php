<?php

namespace Modules\Expenses\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\CreateExpenseCategory;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\EditExpenseCategory;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\ListExpenseCategories;
use Modules\Expenses\Models\ExpenseCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExpenseCategoryResource::class)]
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
            ->test(ListExpenseCategories::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas($record);
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
     * @payload missing: name
     * {}
     */
    public function it_fails_to_create_category_without_required_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $payload = [];

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
        $this->markTestIncomplete();
        /* arrange */

        $record  = ExpenseCategory::factory()->for($this->user->companies()->first())->create(['category_name' => 'Original']);
        $payload = ['category_name' => 'Updated Name'];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditExpenseCategory::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_category_with_empty_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ExpenseCategory::factory()->for($this->user->companies()->first())->create(['category_name' => 'X']);
        $payload = ['category_name' => null];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditExpenseCategory::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['category_name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_expense_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ExpenseCategory::factory()->for($this->user->companies()->first())->create();

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListExpenseCategories::class)->callTableAction('delete', $record);

        /* assert */
        $this->assertDatabaseMissing('expense_categories', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_already_deleted_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ExpenseCategory::factory()->for($this->user->companies()->first())->create();
        $record->delete();

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListExpenseCategories::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('expense_categories', ['id' => $record->id]);
    }
    # endregion
}
