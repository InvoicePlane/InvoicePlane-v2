<?php

namespace Modules\Expenses\Database\Factories;

use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\Company;
use Modules\Expenses\Models\ExpenseCategory;
use RuntimeException;

class ExpenseCategoryFactory extends AbstractFactory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        $company = $this->company ?? Company::query()->inRandomOrder()->first();

        if ( ! $company) {
            throw new RuntimeException('No company available for ExpenseCategory factory');
        }

        static $categories = [
            'Travel', 'Accommodation', 'Meals and Entertainment', 'Office Supplies',
            'Professional Services', 'Utilities', 'Phone and Internet', 'Software Subscriptions',
            'Advertising', 'Insurance', 'Education', 'Maintenance and Repairs',
            'Transportation', 'Bank Fees', 'Legal and Accounting Fees', 'Taxes',
            'Charitable Donations', 'Gifts', 'Memberships and Dues', 'Miscellaneous',
        ];

        return [
            'company_id'    => $company->id,
            'category_name' => $this->faker->randomElement($categories),
        ];
    }
}
