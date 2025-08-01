<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Address;
use Modules\Core\Enums\AddressType;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'type'              => fake()->randomElement(AddressType::cases())->value,
            'address_1'         => fake()->streetAddress,
            'address_2'         => null,
            'number'            => fake()->buildingNumber,
            'postal_code'       => fake()->postcode,
            'city'              => fake()->city,
            'state_or_province' => fake()->stateAbbr,
            'country'           => fake()->countryCode,
        ];
    }

    public function ofType(AddressType $type): self
    {
        return $this->state(['type' => $type->value]);
    }
}
