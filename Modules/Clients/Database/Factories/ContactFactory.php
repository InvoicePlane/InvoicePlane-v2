<?php

namespace Modules\Clients\Database\Factories;

use Faker\Provider\en_US\Address;
use Faker\Provider\en_US\Company;
use Faker\Provider\en_US\Person;
use Faker\Provider\en_US\PhoneNumber;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Modules\Clients\Enums\Gender;
use Modules\Clients\Models\Contact;
use Modules\Core\Database\Factories\AbstractFactory;

class ContactFactory extends AbstractFactory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new Address($this->faker));
        $this->faker->addProvider(new PhoneNumber($this->faker));
        $this->faker->addProvider(new Company($this->faker));
        $this->faker->addProvider(new Lorem($this->faker));
        $this->faker->addProvider(new Internet($this->faker));

        return [
            'first_name' => fake()->firstName,
            'last_name'  => fake()->lastName,
            'gender'     => $this->faker->randomElement(Gender::cases())->value,
        ];
    }
}
