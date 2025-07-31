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
use RuntimeException;

/**
 * @extends Factory<\Modules\Expenses\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $company = $this->company ?? Company::query()->inRandomOrder()->first();

        if ( ! $company) {
            throw new RuntimeException('No company available for Expense factory');
        }

        // Get a customer that belongs to this company
        $customer = Relation::query()
            ->where('company_id', $company->id)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first();

        if ( ! $customer) {
            dd('die early');
        }

        // Get a vendor that belongs to this company
        $vendor = Relation::query()
            ->where('company_id', $company->id)
            ->where('relation_type', RelationType::VENDOR->value)
            ->inRandomOrder()
            ->first();

        if ( ! $vendor) {
            dd('die early');
        }

        // Get a category that belongs to this company
        $category = ExpenseCategory::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first();

        if ( ! $category) {
            dd('die early');
        }

        // Create or get a user that belongs to this company
        $user = \Modules\Core\Models\User::query()
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company->id))
            ->inRandomOrder()
            ->first()
            ??
            \Modules\Core\Models\User::factory()
                ->hasAttached($company)
                ->create();

        return [
            'company_id'     => $company->id,
            'customer_id'    => $customer->id,
            'vendor_id'      => $vendor->id,
            'category_id'    => $category->id,
            'user_id'        => $user->id,
            'expense_number' => $this->faker->unique()->numerify('EXP-#####'),
            'expense_status' => $this->faker->randomElement(ExpenseStatus::cases())->value,
            'expense_type'   => $this->faker->randomElement(ExpenseType::cases())->value,
            'expensed_at'    => $this->faker->dateTimeBetween('-1 years', '-1 month')->format('Y-m-d'),
            'expense_amount' => $this->faker->randomFloat(4, 10, 500),
            'description'    => $this->faker->optional(0.7)->sentence(),
        ];
    }
}
