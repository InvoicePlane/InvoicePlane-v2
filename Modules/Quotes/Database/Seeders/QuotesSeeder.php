<?php

namespace Modules\Quotes\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class QuotesSeeder extends AbstractSeeder
{
    protected string $label = 'Quotes';

    protected int $defaultCount = 10;

    protected function buildOne(): void
    {
        $prospect = $this->findOrCreateProspect($this->companyId);

        $user = $this->findOrCreateUser($this->companyId);

        $quote = Quote::factory()
            ->state([
                'company_id'  => $this->companyId,
                'prospect_id' => $prospect->id,
                'user_id'     => $user->id,
            ])
            ->create();

        $product = $this->findOrCreateProduct($this->companyId);

        QuoteItem::factory()
            ->count(random_int(2, 4))
            ->for($quote)
            ->state(['product_id' => $product->id])
            ->create();
    }
}
