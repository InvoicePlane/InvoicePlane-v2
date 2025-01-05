<?php

namespace Modules\Expenses\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Expenses\Filament\Resources\ExpenseVendorResource\Pages\CreateExpenseVendor;
use Modules\Expenses\Filament\Resources\ExpenseVendorResource\Pages\EditExpenseVendor;
use Modules\Expenses\Filament\Resources\ExpenseVendorResource\Pages\ManageExpenseVendors;
use Modules\Expenses\Models\ExpenseVendor;

class ExpenseVendorsTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->markTestIncomplete('Needs migration for Expenses');
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /** @test */
    public function it_displays_expense_vendors_index(): void
    {
        $user = User::factory()->create();
        ExpenseVendor::factory()->create(['vendor_name' => 'Test Expense Vendor']);

        Livewire::test(ManageExpenseVendors::class)
            ->assertCanSeeTableRecords(ExpenseVendor::all());
    }

    /** @test */
    public function it_creates_an_expense_vendor(): void
    {
        $data = [
            'vendor_name'   => 'Test Vendor',
            'vendor_email'  => 'vendor@example.com',
            'vendor_phone'  => '123456789',
            'vendor_active' => true,
        ];

        Livewire::test(CreateExpenseVendor::class)
            ->set('data.vendor_name', $data['vendor_name'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(ExpenseVendor::class, array_merge($data, [
            'expense_vendor_id' => ExpenseVendor::latest('expense_vendor_id')->first()->expense_vendor_id,
            'created_at'        => now()->toDateTimeString(),
            'updated_at'        => now()->toDateTimeString(),
        ]));
    }

    /** @test */
    public function it_updates_an_expense_vendor(): void
    {
        $expenseVendor = ExpenseVendor::factory()->create([
            'vendor_name' => 'Original Vendor',
        ]);

        $updatedData = [
            'vendor_name' => 'Updated Vendor',
        ];

        Livewire::test(EditExpenseVendor::class, ['record' => $expenseVendor->expense_vendor_id])
            ->set('data.vendor_name', $updatedData['vendor_name'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(ExpenseVendor::class, array_merge($updatedData, [
            'expense_vendor_id' => $expenseVendor->expense_vendor_id,
            'updated_at'        => now()->toDateTimeString(),
        ]));
    }

    /** @test */
    public function it_deletes_an_expense_vendor(): void
    {
        $expenseVendor = ExpenseVendor::factory()->create();

        Livewire::test(ManageExpenseVendors::class)
            ->callTableAction('delete', $expenseVendor);

        $this->assertDatabaseMissing(ExpenseVendor::class, [
            'expense_vendor_id' => $expenseVendor->expense_vendor_id,
        ]);
    }
}
