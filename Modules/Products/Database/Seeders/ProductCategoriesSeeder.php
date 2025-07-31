<?php

namespace Modules\Products\Database\Seeders;

use Exception;
use Illuminate\Support\Facades\Log;
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
        if ( ! $companyId) {
            $this->command->warn('No company ID provided to ProductCategoriesSeeder. Aborting.');

            return;
        }

        $company = Company::query()->find($companyId);
        if ( ! $company) {
            $this->command->warn("Company with ID {$companyId} not found. Aborting ProductCategoriesSeeder.");

            return;
        }

        Log::info("Seeding product categories for company: {$company->name} (ID: {$company->id})");

        $existingCategories = ProductCategory::query()
            ->where('company_id', $company->id)
            ->pluck('category_name')
            ->toArray();

        $created = 0;
        $skipped = 0;

        foreach ($this->defaultCategories as $categoryName) {
            if (in_array($categoryName, $existingCategories, true)) {
                $skipped++;
                continue;
            }

            try {
                ProductCategory::create([
                    'company_id'    => $company->id,
                    'category_name' => $categoryName,
                    'description'   => "Default {$categoryName} category",
                ]);
                $created++;
            } catch (Exception $e) {
                $this->command->warn("Failed to create product category '{$categoryName}': " . $e->getMessage());
                $skipped++;
            }
        }

        Log::info(sprintf(
            'Product categories for %s: %d created, %d already existed',
            $company->name,
            $created,
            $skipped
        ));
    }
}
