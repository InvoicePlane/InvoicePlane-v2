<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Company;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;

class ProductsSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $categories = ProductCategory::query()->where('company_id', $company->id)->get();

            if ($categories->isEmpty()) {
                $this->command->warn("No product categories found for company {$company->name}. Creating some...");
                $categories = ProductCategory::factory()
                    ->count(5)
                    ->create(['company_id' => $company->id]);
            }

            $units = ProductUnit::query()->where('company_id', $company->id)->get();

            if ($units->isEmpty()) {
                $this->command->warn("No product units found for company {$company->name}. Creating some...");
                $units = ProductUnit::factory()
                    ->count(3)
                    ->create(['company_id' => $company->id]);
            }

            $productCount = random_int(10, 30);
            Log::info("Creating {$productCount} products for company: {$company->name}");

            Product::factory()
                ->count($productCount)
                ->for($company)
                ->state([
                    'category_id' => fn () => $categories->random()->id,
                    'unit_id'     => fn () => $units->random()->id,
                ])
                ->create();
        });
    }
}
