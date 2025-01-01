<?php

namespace Modules\Expenses\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Expenses\Models\Expense;

class ExpensesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;
    // endregion

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
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
    public function it_shows_expenses_index(): void
    {
        $user = User::factory()->create();

        Expense::factory()->create([
            'expense_name' => '::expense_name::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('expenses.index'));
        $response->assertStatus(200);
        $response->assertSee('::expense_name::');
    }
    // endregion
}
