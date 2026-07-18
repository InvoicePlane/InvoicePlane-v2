<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Invoices\Enums\RecurringFrequency;
use Modules\Invoices\Models\Invoice;
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
            'company_id' => $company->id,
            'invoice_id' => Invoice::factory()->for($company),
            'frequency'  => fake()->randomElement(RecurringFrequency::cases())->value,
            'start_at'   => fake()->date(),
            'end_at'     => fake()->optional()->date(),
        ];
    }
}
