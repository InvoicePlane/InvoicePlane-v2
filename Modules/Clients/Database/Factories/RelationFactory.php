<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;

/**
 * @extends Factory<\Modules\Core\Models\Relation>
 */
class RelationFactory extends Factory
{
    protected $model = Relation::class;

    public function definition(): array
    {
        $tradingName = $this->faker->optional(0.7)->companySuffix();

        return [
            'company_id'         => Company::query()->inRandomOrder()->first()->id,
            'primary_contact_id' => \Modules\Clients\Models\Contact::query()->inRandomOrder()->first()->id,
            'relation_type'      => $this->faker->randomElement(RelationType::cases())->value,
            'relation_status'    => $this->faker->randomElement(RelationStatus::cases())->value,
            'relation_number'    => $this->faker->bothify('??######'),
            'company_name'       => $this->faker->company,
            'trading_name'       => $tradingName,
            'unique_name'        => Str::slug($tradingName),
            'id_number'          => $this->faker->optional()->numerify('#########'),
            'coc_number'         => $this->faker->optional()->numerify('#########'),
            'vat_number'         => $this->faker->optional()->regexify('^(BE|NL|DE|FR|LU)\d{9}$'),
            'currency_code'      => null,
            'language'           => fake()->optional()->languageCode,
            'registered_at'      => $this->faker->dateTimeBetween('-2 years', '-1 month')->format('Y-m-d'),
        ];
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'relation_type' => RelationType::CUSTOMER->value,
        ]);
    }

    public function vendor(): static
    {
        return $this->state(fn (array $attributes) => [
            'relation_type' => RelationType::VENDOR->value,
        ]);
    }

    public function prospect(): static
    {
        return $this->state(fn (array $attributes) => [
            'relation_type' => RelationType::PROSPECT->value,
        ]);
    }
}
