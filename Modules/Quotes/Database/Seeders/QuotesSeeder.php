<?php

namespace Modules\Quotes\Database\Seeders;

use Modules\Clients\Database\Seeders\CustomersSeeder;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Products\Models\Product;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class QuotesSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public function run(?int $companyId = null): void
    {
        $query = Company::query();

        if ($companyId) {
            $query->where('id', $companyId);
        }

        $query->each(function (Company $company) {
            $existingCount = Quote::query()->where('company_id', $company->id)->count();

            if ($existingCount > 0) {
                $this->command->info("Skipping quotes for company {$company->name} - already has {$existingCount} quotes.");

                return;
            }

            $this->command->info("Creating quotes for company: {$company->name}");

            $customers = Relation::query()->where('company_id', $company->id)
                ->where('relation_type', 'customer')
                ->get();

            if ($customers->isEmpty()) {
                $this->command->warn("No customers found for company {$company->name}. Creating some...");
                $this->call(CustomersSeeder::class, ['companyId' => $company->id]);
                $customers = Relation::query()->where('company_id', $company->id)
                    ->where('relation_type', 'customer')
                    ->get();
            }

            $products = Product::query()->where('company_id', $company->id)->get();

            if ($products->isEmpty()) {
                $this->command->warn("No products found for company {$company->name}. Creating some...");
                $this->call(ProductsSeeder::class, ['companyId' => $company->id]);
                $products = Product::query()->where('company_id', $company->id)->get();
            }

            $taxRates = TaxRate::query()->where('company_id', $company->id)->get();

            if ($taxRates->isEmpty()) {
                $this->command->warn("No tax rates found for company {$company->name}. Using default...");
                $taxRates = collect([
                    TaxRate::factory()->create([
                        'company_id' => $company->id,
                        'item_name'  => 'VAT',
                        'rate'       => 21.0,
                        'is_default' => true,
                    ]),
                ]);
            }

            $quoteCount = rand(10, 30);

            for ($i = 0; $i < $quoteCount; $i++) {
                $quote = $this->createQuote($company, $customers->random(), $products, $taxRates);
                $this->addQuoteItems($quote, $products, $taxRates);
                $calculator = new \Modules\Quotes\Support\QuoteCalculator();
                $calculator->updateAndSave($quote);
            }

            $this->command->info("Created {$quoteCount} quotes for company: {$company->name}");
        });
    }

    protected function createQuote(Company $company, Relation $customer): \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {
        $statuses = [
            QuoteStatus::DRAFT,
            QuoteStatus::SENT,
            QuoteStatus::VIEWED,
            QuoteStatus::APPROVED,
            QuoteStatus::REJECTED,
        ];

        $status     = $statuses[array_rand($statuses)];
        $quoteDate  = now()->subDays(random_int(0, 90));
        $expiryDate = $quoteDate->copy()->addDays(random_int(15, 60));

        // Get a random user from the company to assign as the quote owner
        $user = $company->users->random();

        return Quote::factory()
            ->for($company)
            ->create([
                'prospect_id'            => $customer->id,
                'user_id'                => $user->id,
                'quote_number'           => $this->generateQuoteNumber($company->id),
                'quote_status'           => $status->value,
                'quoted_at'              => $quoteDate,
                'quote_expires_at'       => $expiryDate,
                'quote_discount_amount'  => 0.00,
                'quote_discount_percent' => 0.00,
                'item_tax_total'         => 0.00,
                'quote_item_subtotal'    => 0.00,
                'quote_tax_total'        => 0.00,
                'quote_total'            => 0.00,
                'terms'                  => $this->getRandomTerms(),
                'footer'                 => $this->getRandomFooter(),
            ]);
    }

    protected function addQuoteItems(Quote $quote, $products, $taxRates): void
    {
        $itemCount   = rand(1, 10);
        $productPool = $products->shuffle();

        for ($i = 0; $i < min($itemCount, $productPool->count()); $i++) {
            $product   = $productPool->get($i);
            $quantity  = rand(1, 10);
            $unitPrice = $product->price * (random_int(90, 110) / 100);
            $discount  = rand(0, 1) === 1 ? rand(5, 20) : 0;

            // Get random tax rates if applicable
            $taxRate1   = null;
            $taxRate2   = null;
            $taxRate1Id = null;
            $taxRate2Id = null;

            if (random_int(0, 1) === 1 && $taxRates->isNotEmpty()) {
                $taxRate1   = $taxRates->random();
                $taxRate1Id = $taxRate1->id;

                if (random_int(0, 1) === 1 && $taxRates->count() > 1) {
                    $taxRate2   = $taxRates->where('id', '!=', $taxRate1->id)->random();
                    $taxRate2Id = $taxRate2->id;
                }
            }

            $item = new QuoteItem([
                'company_id'      => $quote->company_id,
                'product_id'      => $product->id,
                'product_unit_id' => $product->product_unit_id,
                'item_name'       => $product->product_name,
                'description'     => $product->description,
                'quantity'        => $quantity,
                'price'           => $unitPrice,
                'discount'        => $discount,
                // Let QuoteCalculator handle all calculations
                'subtotal'      => 0,
                'tax_1'         => 0,
                'tax_2'         => 0,
                'tax_total'     => 0,
                'total'         => 0,
                'tax_rate_id'   => $taxRate1Id,
                'tax_rate_2_id' => $taxRate2Id,
            ]);

            $quote->quoteItems()->save($item);
        }
    }

    protected function generateQuoteNumber(int $companyId): string
    {
        $prefix    = 'QUO-' . date('Y') . '-';
        $lastQuote = Quote::query()->where('company_id', $companyId)
            ->where('quote_number', 'like', $prefix . '%')
            ->orderBy('quote_number', 'desc')
            ->first();

        if ($lastQuote) {
            $lastNumber = (int) str_replace($prefix, '', $lastQuote->quote_number);

            return $prefix . mb_str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '00001';
    }

    protected function getRandomTerms(): string
    {
        $terms = [
            'This quote is valid for 30 days from the date of issue.',
            'Prices are subject to change without notice.',
            'A 50% deposit is required to begin work.',
            'Payment is due within 14 days of quote acceptance.',
            'This quote includes all labor and materials.',
            'Additional charges may apply for work outside the scope of this quote.',
            'This quote is based on current market conditions.',
            'Terms: Net 30 days.',
        ];

        return $terms[array_rand($terms)];
    }

    protected function getRandomFooter(): string
    {
        $footers = [
            'Thank you for your business!',
            'We look forward to working with you.',
            'Please contact us with any questions.',
            'This is a computer-generated quote. No signature required.',
            'Prices are exclusive of applicable taxes.',
        ];

        return $footers[array_rand($footers)];
    }
}
