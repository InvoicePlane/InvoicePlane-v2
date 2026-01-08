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
        $taxRate1 = $this->findOrCreateTaxRate($this->companyId);
        $taxRate2 = $this->findOrCreateTaxRate($this->companyId);

        Product::factory()
            ->state([
                'company_id'    => $this->companyId,
                'category_id'   => $category->id,
                'unit_id'       => $unit->id,
                'tax_rate_id'   => $taxRate1->id,
                'tax_rate_2_id' => $taxRate2->id,
            ])
            ->create();
    }
}
