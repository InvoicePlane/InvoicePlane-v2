<?php

namespace Modules\Quotes\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class QuotesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            foreach ([
                ['quote_status' => 'draft', 'count' => 1],
                ['quote_status' => 'sent', 'count' => 2],
                ['quote_status' => 'viewed', 'count' => 2],
                ['quote_status' => 'approved', 'count' => 3],
                ['quote_status' => 'canceled', 'count' => 2],
            ] as $config) {
                Quote::factory()
                    ->state(['company_id' => $company->id])
                    ->{$config['quote_status']}()
                    ->count($config['count'])
                    ->create()
                    ->each(function (Quote $quote): void {
                        QuoteItem::factory()
                            ->count(random_int(2, 3))
                            ->create(['quote_id' => $quote->id]);
                    });
            }
        });
    }
}
