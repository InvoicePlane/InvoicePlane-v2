<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Log;
use Modules\Clients\Enums\Gender;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;
use RuntimeException;

/**
 * @extends Factory<\Modules\Clients\Models\Contact>
 */
class ContactFactory extends AbstractFactory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        if ($companyId === null) {
            Log::channel('extreme_debug')->error('company_id missing in RelationFactory or ContactFactory!');
            throw new RuntimeException('company_id is required.');
        }

        $relation = Relation::query()->where('relation_type', RelationType::CUSTOMER->value)->inRandomOrder()->first() ?? Relation::factory()->create();

        return [
            'relation_id' => $relation->id,
            'first_name'  => fake()->firstName,
            'last_name'   => fake()->lastName,
            'gender'      => $this->faker->randomElement(Gender::cases())->value,
        ];
    }
}
