<?php

namespace Modules\Products\Database\Seeders;

use Exception;
use Modules\Core\Models\Company;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;

class ProductsSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public function run(?int $companyId = null): void
    {
        if ( ! $companyId) {
            $this->command->error('No company ID provided to ProductsSeeder. Aborting.');

            return;
        }

        $company = Company::find($companyId);
        if ( ! $company) {
            $this->command->error("Company with ID {$companyId} not found. Aborting ProductsSeeder.");

            return;
        }

        $this->command->info("Seeding products for company: {$company->name} (ID: {$company->id})");

        $units = ProductUnit::query()
            ->where('company_id', $company->id)
            ->get();

        if ($units->isEmpty()) {
            dd('NO!');
        }

        $categories = ProductCategory::query()
            ->where('company_id', $company->id)
            ->get();

        if ($categories->isEmpty()) {
            dd('NO!');
        }

        $productCount = random_int(1, 2);
        $this->command->info("Creating {$productCount} products for company: {$company->name}");

        $bar = $this->command->getOutput()->createProgressBar($productCount);
        $bar->start();

        for ($i = 0; $i < $productCount; $i++) {
            try {
                Product::factory()
                    ->for($company)
                    ->for($categories->random(), 'productCategory')
                    ->for($units->random(), 'productUnit')
                    ->create();
            } catch (Exception $e) {
                $this->command->error('Error creating product: ' . $e->getMessage());
                continue;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
    }
}
