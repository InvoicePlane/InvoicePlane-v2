<?php

namespace Modules\Products\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Products\Models\ProductUnit;

class ProductUnitsSeeder extends AbstractSeeder
{
    protected string $label        = 'ProdUnits';
    protected int    $defaultCount = 2;

    protected function buildOne(): void
{
    ProductUnit::factory()
        ->state(['company_id' => $this->companyId])
        ->create();
}
}
