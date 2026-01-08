<?php

namespace Modules\Clients\Database\Factories;

use Faker\Provider\en_US\Address;
use Faker\Provider\en_US\Company;
use Faker\Provider\en_US\Person;
use Faker\Provider\en_US\PhoneNumber;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Communication;

class CommunicationFactory extends Factory
{
    protected $model = Communication::class;

    public function definition(): array
    {
        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new Address($this->faker));
        $this->faker->addProvider(new PhoneNumber($this->faker));
        $this->faker->addProvider(new Company($this->faker));
        $this->faker->addProvider(new Lorem($this->faker));
        $this->faker->addProvider(new Internet($this->faker));

        return [
            'is_primary'          => fake()->boolean(25),
            'communication_type'  => fake()->randomElement(CommunicationType::cases()),
            'communication_value' => fake()->word,
        ];
    }
}
