<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Models\Product;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends AbstractFactory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(4, 100, 1000);
        $taxRate  = 0.20;
        $sign     = $this->faker->boolean(75) ? '1' : '-1';
        $taxTotal = $subtotal * $taxRate;
        $total    = $subtotal + $taxTotal;

        return [
            'invoice_number'           => $this->faker->unique()->numerify('INV-###-####'),
            'invoice_status'           => $this->faker->randomElement(InvoiceStatus::cases())->value,
            'invoice_sign'             => $sign,
            'invoiced_at'              => $this->faker->dateTimeBetween('-3 years', '+4 months')->format('Y-m-d'),
            'invoice_due_at'           => $this->faker->dateTimeBetween('-3 years', '+4 months')->format('Y-m-d'),
            'invoice_discount_amount'  => $this->faker->randomFloat(4, 0, 100),
            'invoice_discount_percent' => $this->faker->randomFloat(4, 0, 25),
            'invoice_item_subtotal'    => $subtotal,
            'item_tax_total'           => $subtotal * $taxRate,
            'invoice_tax_total'        => $taxTotal,
            'invoice_total'            => $total,
            'invoice_password'         => null,
            'url_key'                  => $this->faker->regexify('[A-Za-z0-9]{32}'),
            'is_read_only'             => $this->faker->boolean(10),
            'template'                 => null,
            'summary'                  => null,
            'terms'                    => null,
            'footer'                   => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Invoice $invoice) {
            $product = Product::query()
                ->where('company_id', $invoice->company_id)
                ->inRandomOrder()
                ->first();

            if ( ! $product) {
                $product = Product::factory()
                    ->state(['company_id' => $invoice->company_id])
                    ->create();
            }

            $taxRate = TaxRate::query()
                ->where('company_id', $invoice->company_id)
                ->inRandomOrder()
                ->first();

            if ( ! $taxRate) {
                $taxRate = Product::factory()
                    ->state(['company_id' => $invoice->company_id])
                    ->create();
            }

            InvoiceItem::factory()
                ->count(random_int(1, 4))
                ->state([
                    'company_id'    => $invoice->company_id,
                    'invoice_id'    => $invoice->id,
                    'product_id'    => $product->id,
                    'item_name'     => $product->product_name ?? 'Item',
                    'tax_rate_id'   => $taxRate->id,
                    'tax_rate_2_id' => null,
                ])
                ->create();
        });
    }

    public function draft(): static
    {
        return $this->state(fn () => ['invoice_status' => InvoiceStatus::DRAFT->value]);
    }

    public function paid(): static
    {
        return $this->state(fn () => ['invoice_status' => InvoiceStatus::PAID->value]);
    }

    public function sent(): static
    {
        return $this->state(fn () => ['invoice_status' => InvoiceStatus::SENT->value]);
    }

    public function viewed(): static
    {
        return $this->state(fn () => ['invoice_status' => InvoiceStatus::VIEWED->value]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => ['invoice_status' => InvoiceStatus::OVERDUE->value]);
    }
}
