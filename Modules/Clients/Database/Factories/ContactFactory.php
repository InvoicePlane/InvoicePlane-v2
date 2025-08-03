<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\Gender;
use Modules\Clients\Models\Contact;
use Modules\Core\Database\Factories\AbstractFactory;

/**
 * @extends Factory<\Modules\Clients\Models\Contact>
 */
class ContactFactory extends AbstractFactory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName,
            'last_name'  => fake()->lastName,
            'gender'     => $this->faker->randomElement(Gender::cases())->value,
        ];
    }
}
