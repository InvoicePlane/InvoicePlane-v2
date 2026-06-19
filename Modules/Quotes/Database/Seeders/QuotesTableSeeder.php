<?php

namespace Modules\Quotes\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteItemAmount;

class QuotesTableSeeder extends Seeder
{
    public function run(): void
    {
        Quote::factory()->count(5)->create()->each(function ($quote): void {
            $quote->quoteItems()->saveMany(
                QuoteItem::factory()->count(rand(3, 5))->create()->each(function ($quoteItem): void {
                    $quoteItem->quoteItemAmounts()->saveMany(
                        QuoteItemAmount::factory()->count(rand(3, 5))->create()
                    )->make();
                })
            )->make();
        });
    }
}
