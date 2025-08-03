<?php

namespace Modules\Expenses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Models\Expense;

/**
 * @extends Factory<\Modules\Expenses\Models\Expense>
 */
class ExpenseFactory extends AbstractFactory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'user_id'        => null,
            'expense_number' => $this->faker->unique()->numerify('EXP-#####'),
            'expense_status' => $this->faker->randomElement(ExpenseStatus::cases())->value,
            'expense_type'   => $this->faker->randomElement(ExpenseType::cases())->value,
            'expensed_at'    => $this->faker->dateTimeBetween('-1 years', '-1 month')->format('Y-m-d'),
            'expense_amount' => $this->faker->randomFloat(4, 10, 500),
            'description'    => $this->faker->optional(0.7)->sentence(),
        ];
    }
}
