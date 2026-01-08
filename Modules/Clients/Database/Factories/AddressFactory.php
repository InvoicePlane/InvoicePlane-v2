<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Address;
use Modules\Core\Models\Company;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        return [
            'company_id'        => $company->id,
            'type'              => fake()->word,
            'address_1'         => fake()->optional()->word,
            'address_2'         => fake()->optional()->word,
            'number'            => fake()->optional()->word,
            'postal_code'       => fake()->postcode,
            'city'              => fake()->city,
            'state_or_province' => fake()->optional()->word,
            'country'           => fake()->country,
        ];
    }
}
