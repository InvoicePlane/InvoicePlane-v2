<?php

namespace Modules\Expenses\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Core\tests\ApiTestTrait;
use Modules\Expenses\Models\Expense;

class ExpensesApiTest extends AbstractTestCase
{
    use ApiTestTrait;
    use RefreshDatabase;
    use WithoutMiddleware;

    // region CRUD Tests

    /** @test */
    public function it_returns_expenses_index(): void
    {
        Expense::factory(5)->create([
            'expense_amount' => 100,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('api.expenses.index'));
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'amount',
                    'date',
                    'category',
                    'description',
                ],
            ],
        ]);
        $response->assertJsonFragment(['amount' => 100]);
    }

    // endregion
}
