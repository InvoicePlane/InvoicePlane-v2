<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Support\Str;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Address;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Enums\AddressType;

class RelationFactory extends AbstractFactory
{
    protected $model = Relation::class;

    protected $company;

    public function definition(): array
    {
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

    public function configure(): static
    {
        return $this->afterCreating(function (Relation $relation) {
            $contacts = Contact::factory()
                ->count(random_int(3, 5))
                ->for($relation->company, 'company')
                ->for($relation, 'relation')
                ->state([
                    'company_id'  => $relation->company_id,
                    'relation_id' => $relation->id,
                ])
                ->create();

            Address::factory()
                ->count(random_int(1, 3))
                ->for($relation->company, 'company')
                ->for($relation, 'addressable')
                ->state([
                    'company_id'       => $relation->company_id,
                    'addressable_id'   => $relation->id,
                    'addressable_type' => Relation::class,
                    'address_type'     => $this->faker->randomElement(AddressType::cases())->value,
                ])->create();

            $contacts->each(function (Contact $contact) {
                $communications = Communication::factory()
                    ->count(random_int(1, 3))
                    ->for($contact, 'communicationable')
                    ->state([
                        'company_id'             => $contact->company_id,
                        'communicationable_type' => Contact::class,
                        'communicationable_id'   => $contact->id,
                        'communication_type'     => CommunicationType::class,
                    ])
                    ->create();

                $primaryCommunication = $communications->random();
                $primaryCommunication->update(['is_primary' => true]);
            });

            $primaryContact = $contacts->random();
            $relation->update(['primary_contact_id' => $primaryContact->id]);
        });
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
