<?php

namespace Modules\Clients\Database\Factories;

use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;

class CustomerFactory extends AbstractFactory
{
    protected $model = Relation::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        return [
            'company_id'         => $companyId,
            'primary_contact_id' => null,
            'relation_type'      => $this->faker->randomElement(RelationType::cases())->value,
            'relation_status'    => $this->faker->randomElement(RelationStatus::cases())->value,
            'relation_number'    => $this->faker->bothify('??######'),
            'company_name'       => $this->faker->company,
            'trading_name'       => $this->faker->optional(0.7)->companySuffix(),
            'id_number'          => $this->faker->optional()->numerify('#########'),
            'coc_number'         => $this->faker->optional()->numerify('#########'),
            'vat_number'         => $this->faker->optional()->regexify('^(BE|NL|DE|FR|LU)\d{9}$'),
            'registered_at'      => $this->faker->dateTimeBetween('-2 years', '-1 month')->format('Y-m-d'),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'client_active' => false,
        ]);
    }
}
