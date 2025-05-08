<?php

namespace Modules\Expenses\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ExpenseVendorsTest extends AbstractTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['name' => 'Staples Inc.']
     */
    #[Group('crud')]
    public function it_lists_expense_vendors(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // arrange
        $record = ExpenseVendor::factory()->for($this->user->company)->create(['name' => 'Staples Inc.']);

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListExpenseVendors::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($record);
    }

    #[Test]

    #[Group('crud')]
    public function it_creates_an_expense_vendor(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // arrange
        $payload = ['name' => 'Paper Supplies Ltd.'];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateExpenseVendor::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expense_vendors', $payload);
    }

    #[Test]

    #[Group('crud')]
    public function it_fails_to_create_vendor_without_name(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // arrange
        $payload = [];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateExpenseVendor::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]

    #[Group('crud')]
    public function it_updates_an_expense_vendor(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // arrange
        $record  = ExpenseVendor::factory()->for($this->user->company)->create(['name' => 'Initial']);
        $payload = ['name' => 'Vendor Updated'];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditExpenseVendor::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expense_vendors', $payload);
    }

    #[Test]

    #[Group('crud')]
    public function it_fails_to_update_vendor_with_empty_name(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // arrange
        $record  = ExpenseVendor::factory()->for($this->user->company)->create(['name' => 'X']);
        $payload = ['name' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditExpenseVendor::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]

    #[Group('crud')]
    public function it_deletes_an_expense_vendor(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // arrange
        $record = ExpenseVendor::factory()->for($this->user->company)->create();

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListExpenseVendors::class)->callTableAction('delete', $record);

        // assert
        $this->assertDatabaseMissing('expense_vendors', ['id' => $record->id]);
    }

    #[Test]

    #[Group('crud')]
    public function it_fails_to_delete_already_deleted_vendor(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // arrange
        $record = ExpenseVendor::factory()->for($this->user->company)->create();
        $record->delete();

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListExpenseVendors::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('expense_vendors', ['id' => $record->id]);
    }
}
