<?php

namespace Modules\Payments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        static $methods = [
            'Bank Transfer',
            'Cash',
            'Check',
            'Credit Card',
            'PayPal',
            'Stripe',
        ];

        return [
            'company_id'          => Company::query()->inRandomOrder()->first()->id,
            'payment_method_name' => $this->faker->randomElement($methods),
        ];
    }
}
