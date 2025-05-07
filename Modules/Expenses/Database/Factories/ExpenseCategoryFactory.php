<?php

namespace Modules\Expenses\Database\Factories;

use Modules\Expenses\Models\ExpenseCategory;

use Modules\Core\Support\Results\Expenses;

use Modules\Expenses\Database\Factories\ExpenseCategoryFactory;

use Modules\Core\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        static $categories = [
            'Travel', 'Accommodation', 'Meals and Entertainment', 'Office Supplies',
            'Professional Services', 'Utilities', 'Phone and Internet', 'Software Subscriptions',
            'Advertising', 'Insurance', 'Education', 'Maintenance and Repairs',
            'Transportation', 'Bank Fees', 'Legal and Accounting Fees', 'Taxes',
            'Charitable Donations', 'Gifts', 'Memberships and Dues', 'Miscellaneous',
        ];

        return [
            'company_id'    => Company::query()->inRandomOrder()->first()->id,
            'category_name' => $this->faker->randomElement($categories),
        ];
    }
}
