<?php

namespace Modules\Products\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Products\Models\ProductCategory;

class ProductCategoriesSeeder extends AbstractSeeder
{
    protected string $label = 'ProdCategories';

    protected int    $defaultCount = 5;

    protected function buildOne(): void
    {
        ProductCategory::factory()
            ->state(['company_id' => $this->companyId])
            ->create();
    }
}
