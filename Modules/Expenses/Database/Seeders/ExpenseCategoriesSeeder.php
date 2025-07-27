<?php

namespace Modules\Expenses\Database\Seeders;

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
            $this->command->info("Seeding expense categories for company: {$company->name}");

            $existingCategories = ExpenseCategory::query()
                ->where('company_id', $company->id)
                ->pluck('category_name')
                ->toArray();

            $created = 0;
            $skipped = 0;

            $bar = $this->command->getOutput()->createProgressBar(count($this->defaultCategories));
            $bar->start();

            foreach ($this->defaultCategories as $categoryName) {
                // Skip if this category already exists for the company
                if (in_array($categoryName, $existingCategories, true)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                ExpenseCategory::updateOrCreate(
                    [
                        'company_id'    => $company->id,
                        'category_name' => $categoryName,
                    ],
                    [
                        // Any additional fields can go here
                    ]
                );
                $created++;
                $bar->advance();
            }

            $bar->finish();
            $this->command->newLine(2);
            $this->command->info(sprintf(
                'Expense categories for %s: %d created, %d already existed',
                $company->name,
                $created,
                $skipped
            ));
        });
    }
}
