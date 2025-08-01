<?php

namespace Modules\Quotes\Database\Seeders;

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
        $prospect = $this->findOrCreateProspect($this->companyId);
        if ( ! $prospect) {
            $this->command->warn("[WARN] No prospect for company {$this->companyId}");

            return;
        }

        $product = Product::query()
            ->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->first();

        if ( ! $product) {
            $this->command->warn("[WARN] No product for company {$this->companyId}");

            return;
        }

        $quote = Quote::factory()
            ->state(['company_id' => $this->companyId, 'prospect_id' => $prospect->id])
            ->create();

        QuoteItem::factory()
            ->count(random_int(2, 4))
            ->for($quote)
            ->state(['product_id' => $product->id])
            ->create();

        $this->command->info("[DEBUG] Finished seeding Quote #{$quote->id} for company {$this->companyId}");
    }
}
