<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Invoices\Models\RecurringInvoice;

/**
 * @extends Factory<RecurringInvoice>
 */
class RecurringInvoiceFactory extends Factory
{
    protected $model = RecurringInvoice::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        return [
            'company_id'        => $company->id,
            'customer_id'       => \Modules\Core\Models\Relation::query()->where('relation_type', RelationType::CUSTOMER->value)->inRandomOrder()->first()->id,
            'invoice_id'        => \Modules\Invoices\Models\Invoice::query()->inRandomOrder()->first()->id,
            'document_group_id' => \Modules\Core\Models\DocumentGroup::query()->inRandomOrder()->first()->id,
            'frequency'         => fake()->word,
            'start_at'          => fake()->date(),
            'end_at'            => fake()->optional()->date(),
        ];
    }
}
