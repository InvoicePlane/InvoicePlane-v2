<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\User;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $company = Company::query()
            ->inRandomOrder()
            ->first()
            ?: Company::factory()->create();

        // Create or get a user that belongs to this company
        $user = User::query()
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company->id))
            ->inRandomOrder()
            ->first() ?? User::factory()
            ->hasAttached($company)
            ->create();

        // Create or get a customer that belongs to this company
        $customer = Relation::query()
            ->where('company_id', $company->id)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first() ?? Relation::factory()
            ->for($company)
            ->customer()
            ->create();

        // Create or get a document group that belongs to this company
        $documentGroup = DocumentGroup::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first() ?? DocumentGroup::factory()
            ->for($company)
            ->create();

        $subtotal = $this->faker->randomFloat(4, 100, 1000);
        $taxRate  = 0.20;
        $sign     = $this->faker->boolean(75) ? '1' : '-1';
        $taxTotal = $subtotal * $taxRate;
        $total    = $subtotal + $taxTotal;

        return [
            'company_id'               => $company->id,
            'user_id'                  => $user->id,
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
            'creditinvoice_parent_id'  => null,
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
