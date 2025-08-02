<?php

namespace Modules\Clients\Database\Factories;

use Modules\Clients\Models\Address;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Enums\AddressType;

class AddressFactory extends AbstractFactory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'type'              => fake()->randomElement(AddressType::cases())->value,
            'address_1'         => fake()->streetAddress,
            'address_2'         => fake()->optional(0.7)->secondaryAddress,
            'number'            => fake()->buildingNumber,
            'postal_code'       => fake()->postcode,
            'city'              => fake()->city,
            'state_or_province' => fake()->optional()->stateAbbr,
            'country'           => fake()->countryCode,
        ];
    }

    public function ofType(AddressType $type): self
    {
        return $this->state(['type' => $type->value]);
    }
}
