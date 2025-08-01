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
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $company   = Company::query()->find($companyId);

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
