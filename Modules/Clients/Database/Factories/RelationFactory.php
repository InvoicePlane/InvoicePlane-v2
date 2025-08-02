<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;

/**
 * @extends Factory<Relation>
 */
class RelationFactory extends AbstractFactory
{
    protected $model = Relation::class;

    protected $company;

    public function definition(): array
    {
        $companyId   = $this->resolveCompanyId();
        $companyName = $this->faker->company;
        $suffix      = $this->faker->optional(0.7)->companySuffix();
        $tradingName = $companyName . ($suffix ? " {$suffix}" : '');

        $relationType = $this->faker->boolean(70)
            ? RelationType::CUSTOMER->value
            : $this->faker->randomElement([
                RelationType::PROSPECT->value,
                RelationType::VENDOR->value,
            ]);

        return [
            'relation_type'   => $relationType,
            'relation_status' => $this->faker->randomElement(RelationStatus::cases())->value,
            'relation_number' => $this->faker->bothify('??######'),
            'company_name'    => $companyName,
            'trading_name'    => $tradingName,
            'unique_name'     => Str::slug($tradingName),
            'id_number'       => $this->faker->optional()->numerify('#########'),
            'coc_number'      => $this->faker->optional()->numerify('#########'),
            'vat_number'      => $this->faker->optional()->regexify('^(BE|DE|FR|LU|NL)\d{9}$'),
            'currency_code'   => null,
            'language'        => fake()->optional()->languageCode,
            'registered_at'   => $this->faker->dateTimeBetween('-2 years', '-1 month')->format('Y-m-d'),
        ];
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'relation_type' => RelationType::CUSTOMER->value,
        ]);
    }

    public function prospect(): static
    {
        return $this->state(fn (array $attributes) => [
            'relation_type' => RelationType::PROSPECT->value,
        ]);
    }

    public function vendor(): static
    {
        return $this->state(fn (array $attributes) => [
            'relation_type' => RelationType::VENDOR->value,
        ]);
    }
}
