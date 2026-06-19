<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'invoice_id'       => Invoice::all()->random()->invoice_id,
            'user_id'          => User::all()->random()->user_id,
            'client_id'        => Client::all()->random()->client_id,
            'invoice_group_id' => InvoiceGroup::all()->random()->invoice_group_id,
            'quote_status_id'  => $this->faker->randomElement(
                [
                    QuoteStatus::DRAFT,
                    QuoteStatus::SENT,
                    QuoteStatus::VIEWED,
                    QuoteStatus::APPROVED,
                    QuoteStatus::REJECTED,
                    QuoteStatus::CANCELED,
                ]
            ),
            'quote_date_created'     => $this->faker->date('Y-m-d H:i:s'),
            'quote_date_modified'    => $this->faker->date('Y-m-d H:i:s'),
            'quote_date_expires'     => $this->faker->date('Y-m-d H:i:s'),
            'quote_number'           => $this->faker->numerify('###-###-####'),
            'quote_discount_amount'  => $this->faker->numerify('##.##'),
            'quote_discount_percent' => $this->faker->numerify('##.##'),
            'quote_url_key'          => $this->faker->regexify('[A-Za-z0-9]{32}'),
            'quote_password'         => bcrypt($this->faker->word),
            'notes'                  => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quote_status_id' => QuoteStatus::DRAFT,
            ];
        });
    }

    public function sent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quote_status_id' => QuoteStatus::SENT,
            ];
        });
    }

    public function viewed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quote_status_id' => QuoteStatus::VIEWED,
            ];
        });
    }

    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quote_status_id' => QuoteStatus::APPROVED,
            ];
        });
    }

    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quote_status_id' => QuoteStatus::REJECTED,
            ];
        });
    }

    public function canceled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quote_status_id' => QuoteStatus::CANCELED,
            ];
        });
    }
}
