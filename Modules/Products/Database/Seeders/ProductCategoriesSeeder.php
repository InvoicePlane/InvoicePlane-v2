<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Products\Models\ProductCategory;

class ProductCategoriesSeeder extends Seeder
{
    protected array $defaultCategories = [
        'Automotive',
        'Books & Media',
        'Clothing',
        'Electronics',
        'Food & Beverages',
        'Health & Beauty',
        'Home & Garden',
        'Office Supplies',
        'Sports & Outdoors',
        'Toys & Games',
    ];

    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = ProductCategory::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping product categories for company {$company->name} - already has {$existingCount} categories.");

                return;
            }

            $this->command->info("Creating product categories for company: {$company->name}");

            foreach ($this->defaultCategories as $categoryName) {
                ProductCategory::factory()
                    ->for($company)
                    ->create([
                        'category_name' => $categoryName,
                        'description'   => "Default {$categoryName} category",
                    ]);
            }
        });
    }
}
