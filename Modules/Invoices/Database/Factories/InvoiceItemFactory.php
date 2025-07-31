<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use RuntimeException;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $company = $this->company ?? Company::query()->inRandomOrder()->first();

        if ( ! $company) {
            throw new RuntimeException('No company available for InvoiceItem factory');
        }

        // Get a product that belongs to this company
        $item = Product::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first();

        if ( ! $item) {
            dd('die early');
        }

        // Get a unit that belongs to this company
        $unit = ProductUnit::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first();

        if ( ! $unit) {
            dd('die early');
        }

        // Get a tax rate that belongs to this company
        $taxRate = TaxRate::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first();

        if ( ! $taxRate) {
            dd('die early');
        }

        // Get a second tax rate 75% of the time that belongs to this company
        $taxRate2 = null;
        if ($this->faker->boolean(75)) {
            $taxRate2 = TaxRate::query()
                ->where('company_id', $company->id)
                ->where('id', '!=', $taxRate->id)
                ->inRandomOrder()
                ->first();

            if ( ! $taxRate2) {
                dd('die early');
            }
        }

        $quantity = $this->faker->randomFloat(4, 1, 20);
        $price    = $this->faker->randomFloat(4, 10, 500);
        $discount = $this->faker->randomFloat(4, 0, 50);
        $subtotal = ($quantity * $price) - $discount;

        // Get an invoice that belongs to this company
        $invoice = Invoice::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first();

        if ( ! $invoice) {
            dd('die early');
        }

        // Get a task that belongs to this company
        $task = \Modules\Projects\Models\Task::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first();

        if ( ! $task) {
            dd('die early');
        }

        return [
            'company_id'      => $company->id,
            'invoice_id'      => $invoice->id,
            'product_id'      => $item->id,
            'task_id'         => $task->id,
            'product_unit_id' => $unit->id,
            'added_at'        => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('Y-m-d'),
            'item_name'       => $item->item_name,
            'product_unit'    => fake()->optional()->word,
            'is_recurring'    => false,
            'quantity'        => $quantity,
            'price'           => $price,
            'discount'        => $discount,
            'subtotal'        => $subtotal,
            'tax_1'           => $subtotal,
            'tax_2'           => $subtotal,
            'tax_total'       => $subtotal,
            'total'           => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'tax_rate_id'     => $taxRate->id,
            'tax_rate_2_id'   => $taxRate2?->id,
            'display_order'   => $this->faker->numberBetween(1, 9999),
            'description'     => null,
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
