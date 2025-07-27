<?php

namespace Modules\Expenses\Database\Seeders;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Company;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoriesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    protected array $defaultCategories = [
        'Bank Fees',
        'Hardware',
        'Insurance',
        'Maintenance',
        'Marketing',
        'Meals & Entertainment',
        'Office Supplies',
        'Other',
        'Professional Services',
        'Rent',
        'Software',
        'Subscriptions',
        'Training',
        'Travel',
        'Utilities',
        'Vehicle',
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = ExpenseCategory::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping expense categories for company {$company->name} - already has {$existingCount} categories.");

                return;
            }

            $this->command->info("Creating expense categories for company: {$company->name}");

            $categories = [];

            foreach ($this->defaultCategories as $category) {
                $categories[] = [
                    'company_id'    => $company->id,
                    'category_name' => $category,
                ];
            }

            DB::table('expense_categories')->insert($categories);

            $this->command->info(sprintf(
                'Created %d expense categories for company: %s',
                count($categories),
                $company->name
            ));
        });
    }
}
