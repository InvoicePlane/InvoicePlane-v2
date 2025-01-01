<?php

namespace Modules\Expenses\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Expenses\Filament\Resources\ExpenseCategoryResource\Pages\ManageExpenseCategories;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoriesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    // endregion

    public function setUp(): void
    {
        parent::setUp();
        $this->markTestIncomplete('Needs migration for Expenses');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_expense_categories_index(): void
    {
        $user = User::factory()->create();

        ExpenseCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.expense_categories.index'));
        $response->assertStatus(200);
        $response->assertSee('::category_name::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_displays_expense_categories_index(): void
    {
        $this->markTestIncomplete('Needs migration for Expenses');
        $user = User::factory()->create();
        ExpenseCategory::factory()->create(['category_name' => '::category_name::', ]);

        //$response->assertStatus(200);
        Livewire::test(ManageExpenseCategories::class)
            ->assertCanSeeTableRecords(ExpenseCategory::all());
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_an_expense_category(): void
    {
        $data = [
            'category_name' => '::new_category_name::',
        ];

        Livewire::test(CreateExpenseCategory::class)
            ->set('data.category_name', $data['category_name'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(ExpenseCategory::class, array_merge($data, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_an_expense_category(): void
    {
        $expenseCategory = ExpenseCategory::factory()->create([
            'category_name' => '::old_category_name::',
        ]);

        $updatedData = [
            'category_name' => '::updated_category_name::',
        ];

        Livewire::test(EditExpenseCategory::class, ['record' => $expenseCategory->id])
            ->set('data.category_name', $updatedData['category_name'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(ExpenseCategory::class, array_merge($updatedData, [
            'id'         => $expenseCategory->id,
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_an_expense_category(): void
    {
        $expenseCategory = ExpenseCategory::factory()->create();

        Livewire::test(ManageExpenseCategories::class)
            ->callTableAction('delete', $expenseCategory);

        $this->assertDatabaseMissing(ExpenseCategory::class, ['expense_category_id' => $expenseCategory->expense_category_id]);
    }
}
