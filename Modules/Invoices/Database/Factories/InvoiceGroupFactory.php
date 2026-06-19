<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupFactory extends Factory
{
    protected $model = InvoiceGroup::class;

    public function definition(): array
    {
        return [
            'invoice_group_name'              => $this->faker->word(),
            'invoice_group_identifier_format' => $this->faker->word(),
            'invoice_group_next_id'           => $this->faker->randomNumber(),
            'invoice_group_left_pad'          => $this->faker->randomNumber(),
        ];
    }
}
