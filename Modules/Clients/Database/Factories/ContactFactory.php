<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\Gender;
use Modules\Core\Models\Company;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'company_id'  => Company::query()->inRandomOrder()->first()->id,
            'relation_id' => Relation::query()->inRandomOrder()->first()->id,
            'first_name'  => $this->faker->firstName,
            'last_name'   => $this->faker->lastName,
            'gender'      => $this->faker->randomElement(Gender::cases())->value,
        ];
    }
}
