<?php

namespace Modules\Expenses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $customer = Relation::where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first() ?? Relation::factory()->create(['relation_type' => RelationType::CUSTOMER->value]);

        static $vendors = [
            'Amazon', 'Uber', 'Lyft', 'FedEx', 'Staples',
            'Apple', 'Microsoft', 'Google', 'Zoom', 'Slack',
            'Airbnb', 'WeWork', 'Delta Airlines', 'American Express',
            'Marriott', 'Hilton', 'Shell', 'Chevron', 'Verizon', 'AT&T',
        ];

        $vendor = $this->faker->randomElement($vendors);

        return [
            'company_id'  => Company::query()->inRandomOrder()->first()->id,
            'customer_id' => $customer->id,
            'vendor_id'   => Relation::factory()->state([
                'company_name' => $vendor,
                'trading_name' => $this->faker->boolean(50)
                    ? "{$vendor} {$this->faker->companySuffix()}"
                    : $vendor,
                'relation_type'   => RelationType::VENDOR->value,
                'relation_number' => $this->faker->numerify('##########'),
                'registered_at'   => $this->faker->dateTimeBetween('-1 years', '-1 month')->format('Y-m-d'),
            ]),
            'category_id'    => ExpenseCategory::query()->inRandomOrder()->first()->id,
            'expense_number' => $this->faker->unique()->numerify('EXP-#####'),
            'expense_status' => $this->faker->randomElement(ExpenseStatus::cases())->value,
            'expense_type'   => $this->faker->randomElement(ExpenseType::cases())->value,
            'expense_amount' => $this->faker->randomFloat(2, 10, 500),
            'description'    => null,
        ];
    }
}
