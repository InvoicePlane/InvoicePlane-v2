<?php

namespace Modules\Clients\Database\Factories;

use Faker\Provider\en_US\Company;
use Faker\Provider\en_US\Person;
use Faker\Provider\en_US\PhoneNumber;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Modules\Clients\Models\Address;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Enums\AddressType;

class AddressFactory extends AbstractFactory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new \Faker\Provider\en_US\Address($this->faker));
        $this->faker->addProvider(new PhoneNumber($this->faker));
        $this->faker->addProvider(new Company($this->faker));
        $this->faker->addProvider(new Lorem($this->faker));
        $this->faker->addProvider(new Internet($this->faker));

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
