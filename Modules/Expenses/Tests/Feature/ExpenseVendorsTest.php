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
    public function it_lists_expense_vendors(): void
    {
        $this->markTestIncomplete();

        // arrange
        $record = ExpenseVendor::factory()->for($this->user->company)->create(['name' => 'Staples Inc.']);

        // act + assert
        Livewire::test(ListExpenseVendors::class)
            ->actingAs($this->user)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Paper Supplies Ltd.']
     */
    public function it_creates_an_expense_vendor(): void
    {
        $this->markTestIncomplete();

        // arrange
        $payload = ['name' => 'Paper Supplies Ltd.'];

        // act
        Livewire::test(CreateExpenseVendor::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expense_vendors', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_create_vendor_without_name(): void
    {
        $this->markTestIncomplete();

        // arrange
        $payload = [];

        // act
        Livewire::test(CreateExpenseVendor::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Vendor Updated']
     */
    public function it_updates_an_expense_vendor(): void
    {
        $this->markTestIncomplete();

        // arrange
        $record  = ExpenseVendor::factory()->for($this->user->company)->create(['name' => 'Initial']);
        $payload = ['name' => 'Vendor Updated'];

        // act
        Livewire::test(EditExpenseVendor::class, ['record' => $record->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('expense_vendors', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => null]
     */
    public function it_fails_to_update_vendor_with_empty_name(): void
    {
        $this->markTestIncomplete();

        // arrange
        $record  = ExpenseVendor::factory()->for($this->user->company)->create(['name' => 'X']);
        $payload = ['name' => null];

        // act
        Livewire::test(EditExpenseVendor::class, ['record' => $record->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_an_expense_vendor(): void
    {
        $this->markTestIncomplete();

        // arrange
        $record = ExpenseVendor::factory()->for($this->user->company)->create();

        // act
        Livewire::test(ListExpenseVendors::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $record);

        // assert
        $this->assertDatabaseMissing('expense_vendors', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_already_deleted_vendor(): void
    {
        $this->markTestIncomplete();

        // arrange
        $record = ExpenseVendor::factory()->for($this->user->company)->create();
        $record->delete();

        // act + assert
        Livewire::test(ListExpenseVendors::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $record)
            ->assertHasErrors();

        $this->assertDatabaseMissing('expense_vendors', ['id' => $record->id]);
    }
}
