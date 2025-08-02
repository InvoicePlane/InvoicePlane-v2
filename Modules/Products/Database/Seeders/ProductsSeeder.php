<?php

namespace Modules\Products\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Products\Models\Product;

class ProductsSeeder extends AbstractSeeder
{
    protected string $label = 'Products';

    protected int $defaultCount = 25;

    protected function buildOne(): void
    {
        $category = $this->findOrCreateProductCategory($this->companyId);
        $unit     = $this->findOrCreateProductUnit($this->companyId);

        Product::factory()
            ->state([
                'company_id'          => $this->companyId,
                'product_category_id' => $category->id,
                'product_unit_id'     => $unit->id,
            ])
            ->create();
    }
}
