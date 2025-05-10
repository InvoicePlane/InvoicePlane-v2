<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();
        $item    = Product::query()->inRandomOrder()->first() ?? Product::factory()->create();
        $unit    = ProductUnit::query()->inRandomOrder()->first() ?? ProductUnit::factory()->create();
        $taxRate = TaxRate::query()->inRandomOrder()->first() ?? TaxRate::factory()->create();

        $calcTaxRate = TaxRate::query()->inRandomOrder()->first() ?? TaxRate::factory()->create();
        $taxRate2    = $this->faker->boolean(75) ? $calcTaxRate : null;

        $quantity = $this->faker->randomFloat(4, 1, 20);
        $price    = $this->faker->randomFloat(4, 10, 500);
        $discount = $this->faker->randomFloat(4, 0, 50);
        $subtotal = ($quantity * $price) - $discount;

        return [
            'company_id'    => $company->id,
            'invoice_id'    => Invoice::query()->inRandomOrder()->first()?->id,
            'item_id'       => $item->id,
            'unit_id'       => $unit->id,
            'added_at'      => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('Y-m-d'),
            'item_name'     => $item->item_name,
            'is_recurring'  => false,
            'quantity'      => $quantity,
            'price'         => $price,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'tax_1'         => $subtotal,
            'tax_2'         => $subtotal,
            'tax'           => $subtotal,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => $taxRate2?->id,
            'display_order' => $this->faker->numberBetween(1, 9999),
            'description'   => null,
        ];
    }

    public function discounted(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->randomFloat(4, 1, 20);
            $price    = $this->faker->randomFloat(4, 10, 500);
            $discount = $this->faker->randomFloat(4, 50, $price * $quantity * 0.5);
            $subtotal = ($quantity * $price) - $discount;

            return [
                'quantity' => $quantity,
                'price'    => $price,
                'discount' => $discount,
                'subtotal' => $subtotal,
            ];
        });
    }
}
