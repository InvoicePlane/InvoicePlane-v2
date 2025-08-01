<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Invoices\Models\RecurringInvoice;

/**
 * @extends Factory<RecurringInvoice>
 */
class RecurringInvoiceFactory extends Factory
{
    protected $model = RecurringInvoice::class;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $company   = Company::query()->find($companyId);

        return [
            'company_id'        => $company->id,
            'customer_id'       => Relation::query()->where('relation_type', RelationType::CUSTOMER->value)->inRandomOrder()->first()->id,
            'invoice_id'        => null,
            'document_group_id' => DocumentGroup::query()->inRandomOrder()->first()->id,
            'frequency'         => fake()->word,
            'start_at'          => fake()->date(),
            'end_at'            => fake()->optional()->date(),
        ];
    }
}
