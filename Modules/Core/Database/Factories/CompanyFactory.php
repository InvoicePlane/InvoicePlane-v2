<?php

namespace Modules\Core\Database\Factories;

use Faker\Provider\en_US\Address;
use Faker\Provider\en_US\Company;
use Faker\Provider\en_US\Person;
use Faker\Provider\en_US\PhoneNumber;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company as CompanyModel;

/**
 * @extends Factory<\Modules\Core\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = CompanyModel::class;

    public function definition(): array
    {
        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new Address($this->faker));
        $this->faker->addProvider(new PhoneNumber($this->faker));
        $this->faker->addProvider(new Company($this->faker));
        $this->faker->addProvider(new Lorem($this->faker));
        $this->faker->addProvider(new Internet($this->faker));

        $companyName = $this->faker->unique()->company;

        return [
            'search_code' => mb_strtoupper(Str::random(5)),
            'name'        => $companyName,
            'slug'        => Str::slug($companyName),
            'vat_number'  => $this->faker->optional()->regexify('^(BE|NL|DE|FR|LU)\d{9}$'),
            'id_number'   => $this->faker->optional()->numerify('#########'),
            'coc_number'  => $this->faker->optional()->numerify('#########'),
        ];
    }

    public function admin(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => UserRole::ADMIN->value,
            ];
        });
    }

    public function guestReadOnly(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => UserRole::CUSTOMER->value,
            ];
        });
    }
}
