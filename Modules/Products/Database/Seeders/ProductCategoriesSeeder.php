<?php

namespace Modules\Products\Database\Seeders;

use Modules\Core\Models\Company;
use Modules\Products\Models\ProductCategory;

class ProductCategoriesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
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
            $this->command->info("Seeding product categories for company: {$company->name}");

            // Get existing categories for this company
            $existingCategories = ProductCategory::query()
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

                ProductCategory::updateOrCreate(
                    [
                        'company_id'    => $company->id,
                        'category_name' => $categoryName,
                    ],
                    [
                        'description' => "Default {$categoryName} category",
                    ]
                );
                $created++;
                $bar->advance();
            }

            $bar->finish();
            $this->command->newLine(2);
            $this->command->info(sprintf(
                'Product categories for %s: %d created, %d already existed',
                $company->name,
                $created,
                $skipped
            ));
        });
    }
}
