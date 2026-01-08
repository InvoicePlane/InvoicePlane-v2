<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Communication;
use Modules\Core\Models\Company;

/**
 * @extends Factory<Communication>
 */
class CommunicationFactory extends Factory
{
    protected $model = Communication::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        return [
            'company_id'             => $company->id,
            'communicationable_type' => fake()->word,
            'communicationable_id'   => null,
            'is_primary'             => fake()->boolean(25),
            'contactable_type'       => fake()->word,
            'contactable_value'      => fake()->word,
        ];
    }
}
