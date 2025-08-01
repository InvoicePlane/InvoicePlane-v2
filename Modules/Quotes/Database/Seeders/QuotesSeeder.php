<?php

namespace Modules\Quotes\Database\Seeders;

use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Products\Models\Product;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class QuotesSeeder extends AbstractSeeder
{
    protected string $label = 'Quotes';

    protected int    $defaultCount = 10;

    protected function buildOne(): void
    {
        $client = Relation::query()
            ->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->firstOrFail();
        $quote = Quote::factory()
            ->state(['company_id' => $this->companyId, 'client_id' => $client->id])
            ->create();
        $product = Product::query()
            ->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->firstOrFail();

        QuoteItem::factory()
            ->count(random_int(2, 4))
            ->for($quote)
            ->state(['product_id' => $product->id])
            ->create();
    }
}
