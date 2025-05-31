<?php

namespace Modules\Expenses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Expenses\Models\ExpenseItem;
use Modules\Invoices\Models\Invoice;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;

/**
 * @extends Factory<\Modules\Expenses\Models\ExpenseItem>
 */
class ExpenseItemFactory extends Factory
{
    protected $model = ExpenseItem::class;

    public function definition(): array
    {
        $company   = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();
        $invoiceId = $this->faker->boolean(25) ? Invoice::query()->inRandomOrder()->first()?->id ?? Invoice::factory()->create()->id : null;
        $item      = Product::query()->inRandomOrder()->first() ?? Product::factory()->create();
        $unit      = ProductUnit::query()->inRandomOrder()->first() ?? ProductUnit::factory()->create();
        $taxRate   = TaxRate::query()->inRandomOrder()->first() ?? TaxRate::factory()->create();

        $calcTaxRate = TaxRate::query()->inRandomOrder()->first() ?? TaxRate::factory()->create();
        $taxRate2    = $this->faker->boolean(75) ? $calcTaxRate : null;

        $quantity = $this->faker->randomFloat(4, 1, 20);
        $price    = $this->faker->randomFloat(4, 10, 500);
        $discount = $this->faker->randomFloat(4, 0, 50);
        $subtotal = ($quantity * $price) - $discount;

        $taxCalc1 = ($taxRate->rate * $subtotal);
        $taxCalc2 = ($taxRate2?->rate * $subtotal);

        $taxCalcTotal = $taxCalc1 + $taxCalc2;

        $total = $subtotal + $taxCalcTotal;

        return [
            'company_id'    => $company->id,
            'invoice_id'    => $invoiceId,
            'item_id'       => $item->id,
            'unit_id'       => $unit->id,
            'added_at'      => $this->faker->dateTimeBetween('-3 years', 'yesterday')->format('Y-m-d'),
            'item_name'     => $item->item_name,
            'is_recurring'  => false,
            'quantity'      => $quantity,
            'price'         => $price,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'tax_1'         => $taxCalc1,
            'tax_2'         => $taxCalc2,
            'tax_total'     => $taxCalcTotal,
            'total'         => $total,
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

            $maxDiscount = $quantity * $price * 0.15;
            $discount    = $this->faker->randomFloat(4, 0, $maxDiscount);

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
