<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invoices\Models\RecurringInvoiceItem;

/**
 * @extends Factory<\Modules\Invoices\Models\RecurringInvoiceItem>
 */
class RecurringInvoiceItemFactory extends Factory
{
    protected $model = RecurringInvoiceItem::class;

    public function definition(): array
    {
        return [
            'recurring_invoice_id' => \Modules\Invoices\Models\RecurringInvoice::query()->inRandomOrder()->first()->id,
            'item_id'              => \Modules\Products\Models\Product::query()->inRandomOrder()->first()->id,
            'tax_rate_id'          => \Modules\Core\Models\TaxRate::query()->inRandomOrder()->first()->id,
            'tax_rate_2_id'        => \Modules\Core\Models\TaxRate::query()->inRandomOrder()->first()->id,
            'item_name'            => fake()->word,
            'quantity'             => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'price'                => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'subtotal'             => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'tax_1'                => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'tax_2'                => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'tax_total'            => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'total'                => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'display_order'        => fake()->randomNumber(),
            'description'          => null,
        ];
    }
}
