<?php

namespace Modules\Products\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Products\Models\Product;

class ProductsSeeder extends AbstractSeeder
{
    protected string $label = 'Products';

    protected int    $defaultCount = 10;

    protected function buildOne(): void
    {
        Product::factory()
            ->state(['company_id' => $this->companyId])
            ->create();
    }
}
