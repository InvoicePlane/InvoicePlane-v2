<?php

namespace Modules\Clients\Database\Factories;

use Modules\Clients\Database\Factories\ContactFactory;

use Modules\Clients\Models\Contact;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Core\Enums\Gender;

use Modules\Clients\Models\Relation;

use Illuminate\Database\Eloquent\Factories\Factory;

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
