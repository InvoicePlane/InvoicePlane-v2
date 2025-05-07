<?php

namespace Modules\Quotes\Database\Seeders;

use Modules\Quotes\Database\Seeders\QuotesSeeder;

use Modules\Core\Support\Results\Quotes;

use Modules\Quotes\Models\Quote;

use Modules\Quotes\Models\QuoteItem;

use Modules\Core\Models\Company;

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
                ['quote_status' => 'draft', 'count' => 10],
                ['quote_status' => 'sent', 'count' => 15],
                ['quote_status' => 'viewed', 'count' => 25],
                ['quote_status' => 'approved', 'count' => 30],
                ['quote_status' => 'canceled', 'count' => 12],
            ] as $config) {
                Quote::factory()
                    ->state(['company_id' => $company->id])
                    ->{$config['quote_status']}()
                    ->count($config['count'])
                    ->create()
                    ->each(function (Quote $quote): void {
                        QuoteItem::factory()
                            ->count(random_int(2, 5))
                            ->create(['quote_id' => $quote->id]);
                    });
            }
        });
    }
}
