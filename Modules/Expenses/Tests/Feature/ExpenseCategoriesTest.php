<?php

namespace Modules\Expenses\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\CreateExpenseCategory;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\EditExpenseCategory;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\ListExpenseCategories;
use Modules\Expenses\Models\ExpenseCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExpenseCategoryResource::class)]
class ExpenseCategoriesTest extends AbstractTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['name' => 'Travel']
     */
    #[Group('crud')]
    public function it_lists_expense_categories(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ExpenseCategory::factory()->for($this->user->company)->create(['name' => 'Travel']);

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListExpenseCategories::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_an_expense_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = ['name' => 'Meals'];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateExpenseCategory::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_category_without_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = [];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateExpenseCategory::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_expense_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ExpenseCategory::factory()->for($this->user->company)->create(['name' => 'Original']);
        $payload = ['name' => 'Updated Name'];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditExpenseCategory::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expense_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_category_with_empty_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ExpenseCategory::factory()->for($this->user->company)->create(['name' => 'X']);
        $payload = ['name' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditExpenseCategory::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_expense_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ExpenseCategory::factory()->for($this->user->company)->create();

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListExpenseCategories::class)->callTableAction('delete', $record);

        // assert
        $this->assertDatabaseMissing('expense_categories', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_already_deleted_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ExpenseCategory::factory()->for($this->user->company)->create();
        $record->delete();

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListExpenseCategories::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('expense_categories', ['id' => $record->id]);
    }
}
