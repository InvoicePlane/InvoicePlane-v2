<?php

namespace Modules\Expenses\Database\Seeders;

use Exception;
use Illuminate\Support\Facades\Log;
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
        if (!$companyId) {
            Log::debug('No company ID provided to ExpenseCategoriesSeeder. Aborting.');
            return;
        }

        $company = Company::query()->find($companyId);
        if (!$company) {
            Log::debug("Company with ID {$companyId} not found. Aborting ExpenseCategoriesSeeder.");
            return;
        }

        Log::info("Seeding expense categories for company: {$company->name}");

        $categoriesToUpsert = array_map(
            fn($name) => [
                'company_id' => $company->id,
                'category_name' => $name,
            ],
            array_values($this->defaultCategories)
        );

        ExpenseCategory::upsert(
            $categoriesToUpsert,
            ['company_id', 'category_name'],
            []
        );

        Log::info(sprintf(
            'Upserted %d expense categories for company: %s',
            count($categoriesToUpsert),
            $company->name
        ));
    }
}
