<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Address;
use Modules\Clients\Models\Addressable;
use Modules\Core\Models\Company;

/**
 * @extends Factory<Addressable>
 */
class AddressableFactory extends Factory
{
    protected $model = Addressable::class;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $company   = Company::query()->find($companyId);
        $address   = Address::query()->inRandomOrder()->first() ?? Address::query()->create();

        return [
            'company_id'       => $company->id,
            'address_id'       => $address->id,
            'addressable_type' => fake()->word,
            'addressable_id'   => null,
            'type'             => fake()->word,
            'is_primary'       => fake()->boolean(25),
        ];
    }
}
