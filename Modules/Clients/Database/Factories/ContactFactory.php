<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\Gender;
use Modules\Core\Models\Company;

/**
 * @extends Factory<\Modules\Clients\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $company  = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();
        $relation = Relation::query()->where('relation_type', RelationType::CUSTOMER->value)->inRandomOrder()->first() ?? Relation::factory()->create();

        return [
            'company_id'  => $company->id,
            'relation_id' => $relation->id,
            'first_name'  => fake()->firstName,
            'last_name'   => fake()->lastName,
            'gender'      => $this->faker->randomElement(Gender::cases())->value,
        ];
    }
}
