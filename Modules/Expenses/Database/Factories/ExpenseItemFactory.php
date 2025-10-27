<?php

namespace Modules\Expenses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Expenses\Models\ExpenseItem;
use Modules\Invoices\Models\Invoice;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use RuntimeException;

class ExpenseItemFactory extends Factory
{
    protected $model = ExpenseItem::class;

    public function definition(): array
    {
        $company = $this->company ?? Company::query()->inRandomOrder()->first();

        if ( ! $company) {
            throw new RuntimeException('No company available for ExpenseItem factory');
        }

        // Get an invoice that belongs to this company if needed
        $invoiceId = null;
        if ($this->faker->boolean(25)) {
            $invoice = Invoice::query()
                ->where('company_id', $company->id)
                ->inRandomOrder()
                ->first();

            if ($invoice) {
                $invoiceId = $invoice->id;
            }
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
