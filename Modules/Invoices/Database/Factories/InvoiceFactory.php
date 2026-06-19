<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Models\PaymentMethod;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'client_id'         => Client::factory(),
            'invoice_group_id'  => InvoiceGroup::all()->random()->invoice_group_id,
            'invoice_status_id' => $this->faker->randomElement([
                Invoice::DRAFT,
                Invoice::SENT,
                Invoice::VIEWED,
                Invoice::PAID,
                Invoice::OVERDUE,
            ]),
            'is_read_only'             => $this->faker->boolean(90),
            'invoice_password'         => null,
            'invoice_date_created'     => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('Y-m-d'),
            'invoice_time_created'     => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('H:i:s'),
            'invoice_date_modified'    => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('Y-m-d H:i:s'),
            'invoice_date_due'         => $this->faker->dateTimeBetween('-3 years', '+2 months')->format('Y-m-d H:i:s'),
            'invoice_number'           => $this->faker->numerify('###-###-####'),
            'invoice_discount_amount'  => $this->faker->numerify('##.##'),
            'invoice_discount_percent' => $this->faker->numerify('##.##'),
            'invoice_terms'            => $this->faker->sentence(10),
            'invoice_url_key'          => $this->faker->regexify('[A-Za-z0-9]{32}'),
            'payment_method'           => PaymentMethod::all()->random()->payment_method_id,
            'creditinvoice_parent_id'  => null,
        ];
    }

    public function draft(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'invoice_status_id' => Invoice::DRAFT,
            ];
        });
    }

    public function sent(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'invoice_status_id' => Invoice::SENT,
            ];
        });
    }

    public function viewed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'invoice_status_id' => Invoice::VIEWED,
            ];
        });
    }

    public function paid(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'invoice_status_id' => Invoice::PAID,
            ];
        });
    }

    public function overdue(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'invoice_status_id' => Invoice::OVERDUE,
            ];
        });
    }
}
