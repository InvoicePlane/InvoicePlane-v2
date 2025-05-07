<?php

namespace Modules\Expenses\Database\Factories;

use Modules\Products\Models\ProductUnit;

use Modules\Invoices\Models\Invoice;

use Modules\Expenses\Database\Factories\ExpenseItemFactory;

use Modules\Core\Models\TaxRate;

use Modules\Core\Support\Results\Expenses;

use Modules\Expenses\Models\ExpenseItem;

use Modules\Products\Models\Product;

use Modules\Core\Models\Company;

use Modules\Core\Support\Results\Invoices;

use Illuminate\Database\Eloquent\Factories\Factory;


class ExpenseItemFactory extends Factory
{
    protected $model = ExpenseItem::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();
        $item    = Product::query()->inRandomOrder()->first() ?? Product::factory()->create();
        $unit    = ProductUnit::query()->inRandomOrder()->first() ?? ProductUnit::factory()->create();
        $taxRate = TaxRate::query()->inRandomOrder()->first() ?? TaxRate::factory()->create();

        $quantity = $this->faker->randomFloat(2, 1, 20);
        $price    = $this->faker->randomFloat(2, 10, 500);
        $discount = $this->faker->randomFloat(2, 0, 50);
        $subtotal = ($quantity * $price) - $discount;

        return [
            'company_id'   => $company->id,
            'invoice_id'   => Invoice::query()->inRandomOrder()->first()?->id,
            'item_id'      => $item->id,
            'unit_id'      => $unit->id,
            'added_at'     => $this->faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'item_name'    => $item->item_name,
            'is_recurring' => false,
            'quantity'     => $quantity,
            'price'        => $price,
            'discount'     => $discount,
            'subtotal'     => $subtotal,
            'tax_rate_id'  => $taxRate->id,
            'order'        => $this->faker->numberBetween(1, 9999),
            'description'  => null,
        ];
    }

    public function discounted(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->randomFloat(2, 1, 20);
            $price    = $this->faker->randomFloat(2, 10, 500);
            $discount = $this->faker->randomFloat(2, 50, $price * $quantity * 0.5);
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
