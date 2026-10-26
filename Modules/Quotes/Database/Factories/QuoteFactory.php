<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Support\Str;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\TaxRate;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class QuoteFactory extends AbstractFactory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $subtotal        = 300;
        $itemTaxTotal    = 0;
        $taxTotal        = 60;
        $discountAmount  = 0;
        $discountPercent = 0;
        $total           = $subtotal + $taxTotal - $discountAmount;

        $quotedAt  = fake()->dateTimeBetween('-1 year', 'now');
        $expiresAt = (clone $quotedAt)->modify('+' . fake()->numberBetween(7, 180) . ' days');

        $companyId = $this->resolveCompanyId();

        return [
            'prospect_id'            => $this->resolveForeignKey(\Modules\Clients\Models\Relation::class, $companyId),
            'user_id'                => $this->resolveForeignKey(\Modules\Core\Models\User::class, $companyId),
            'quote_number'           => 'Q-' . now()->year . '-' . fake()->unique()->numberBetween(1, 9999),
            'quote_status'           => fake()->randomElement(QuoteStatus::cases())->value,
            'quoted_at'              => $quotedAt,
            'quote_expires_at'       => $expiresAt,
            'quote_discount_amount'  => $discountAmount,
            'quote_discount_percent' => $discountPercent,
            'item_tax_total'         => $itemTaxTotal,
            'quote_item_subtotal'    => $subtotal,
            'quote_tax_total'        => $taxTotal,
            'quote_total'            => $total,
            'quote_password'         => null,
            'url_key'                => Str::random(32),
            'template'               => null,
            'summary'                => null,
            'terms'                  => null,
            'footer'                 => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Quote $quote) {
            $products = Product::query()
                ->where('company_id', $quote->company_id)
                ->take(random_int(2, 5))
                ->get();

            if (empty($products)) {
                $product = Product::factory()
                    ->state(['company_id' => $quote->company_id])
                    ->create();
                $products = collect($product);
            }

            $productUnit = ProductUnit::query()
                ->where('company_id', $quote->company_id)
                ->inRandomOrder()
                ->first();

            if ( ! $productUnit) {
                $productUnit = ProductUnit::factory()
                    ->state(['company_id' => $quote->company_id])
                    ->create();
            }

            $taxRate = TaxRate::query()
                ->where('company_id', $quote->company_id)
                ->inRandomOrder()
                ->first();

            if ( ! $taxRate) {
                $taxRate = TaxRate::factory()
                    ->state(['company_id' => $quote->company_id])
                    ->create();
            }

            $products->each(callback: function (Product $product) use ($productUnit, $quote, $taxRate) {
                QuoteItem::factory()
                    ->for($product)
                    ->state([
                        'company_id'      => $quote->company_id,
                        'quote_id'        => $quote->id,
                        'product_id'      => $product->id,
                        'product_unit_id' => $productUnit->id,
                        'item_name'       => $product->product_name ?? 'Item',
                        'tax_rate_id'     => $taxRate->id,
                        'tax_rate_2_id'   => null,
                    ])
                    ->create();
            });
        });
    }

    public function draft(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::DRAFT->value]);
    }

    public function sent(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::SENT->value]);
    }

    public function viewed(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::VIEWED->value]);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::APPROVED->value]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::REJECTED->value]);
    }
}
