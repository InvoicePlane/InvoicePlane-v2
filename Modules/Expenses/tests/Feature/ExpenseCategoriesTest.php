<?php

namespace Modules\Expenses\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoriesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    // endregion

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /**
     * @test
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
     */
    public function it_creates_an_expense_category(): void
    {
        $user = User::factory()->create();

        $payload = [
            'category_name' => '::new_category_name::',
        ];

        $response = $this->actingAs(user: $user, guard: 'web')->post(route('filament.ivpl.resources.filament.resources.expense_categories.store'), $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expense_categories', ['category_name' => '::new_category_name::']);
    }

    /**
     * @test
     */
    public function it_updates_an_expense_category(): void
    {
        $user = User::factory()->create();

        $category = ExpenseCategory::factory()->create([
            'category_name' => '::old_category_name::',
        ]);

        $payload = [
            'category_name' => '::updated_category_name::',
        ];

        $response = $this->actingAs(user: $user, guard: 'web')->put(route('filament.ivpl.resources.filament.resources.expense_categories.update', ['expense_category' => $category->id]), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('expense_categories', ['category_name' => '::updated_category_name::']);
    }

    /**
     * @test
     */
    public function it_deletes_an_expense_category(): void
    {
        $user = User::factory()->create();

        $category = ExpenseCategory::factory()->create();

        $response = $this->actingAs(user: $user, guard: 'web')->delete(route('filament.ivpl.resources.filament.resources.expense_categories.destroy', ['expense_category' => $category->id]));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('expense_categories', ['id' => $category->id]);
    }
}
