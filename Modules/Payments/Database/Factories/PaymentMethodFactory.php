<?php

namespace Modules\Payments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'payment_method_name' => $this->faker->word(),
        ];
    }
}
