<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Communication;

/**
 * @extends Factory<Communication>
 */
class CommunicationFactory extends Factory
{
    protected $model = Communication::class;

    public function definition(): array
    {
        return [
            'is_primary'          => fake()->boolean(25),
            'communication_type'  => fake()->randomElement(CommunicationType::cases()),
            'communication_value' => fake()->word,
        ];
    }
}
