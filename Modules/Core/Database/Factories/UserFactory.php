<?php

namespace Modules\Core\Database\Factories;

use Faker\Provider\en_US\Address;
use Faker\Provider\en_US\Company;
use Faker\Provider\en_US\Person;
use Faker\Provider\en_US\PhoneNumber;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new Address($this->faker));
        $this->faker->addProvider(new PhoneNumber($this->faker));
        $this->faker->addProvider(new Company($this->faker));
        $this->faker->addProvider(new Lorem($this->faker));
        $this->faker->addProvider(new Internet($this->faker));

        $createdAt = $this->faker->dateTimeBetween('-18 months', '-3 months');
        $updatedAt = $createdAt;

        if ($this->faker->boolean()) {
            $updatedAt = $this->faker->dateTimeBetween($createdAt, 'now');
        }

        return [
            'user_type' => $this->faker->randomElement([
                User::ADMIN,
                User::CLIENT,
            ]),
            'user_active'              => $this->faker->boolean,
            'user_date_created'        => $createdAt,
            'user_date_modified'       => $updatedAt,
            'user_language'            => 'system',
            'user_name'                => $this->faker->userName,
            'user_company'             => $this->faker->company,
            'user_address_1'           => $this->faker->streetAddress,
            'user_address_2'           => null,
            'user_city'                => $this->faker->city,
            'user_state'               => null,
            'user_zip'                 => $this->faker->postcode,
            'user_country'             => $this->faker->country,
            'user_phone'               => $this->faker->phoneNumber,
            'user_fax'                 => $this->faker->phoneNumber,
            'user_mobile'              => $this->faker->phoneNumber,
            'user_email'               => $this->faker->unique()->safeEmail,
            'user_password'            => Hash::make(Str::random(16)),
            'user_web'                 => $this->faker->url,
            'user_vat_id'              => $this->faker->numerify('##########'),
            'user_tax_code'            => $this->faker->numerify('###-###-####'),
            'user_psalt'               => Str::random(10),
            'user_all_clients'         => $this->faker->boolean(45),    // Boolean!
            'user_passwordreset_token' => Str::random(32),
            'user_subscribernumber'    => null,
            'user_iban'                => null,
            'user_gln'                 => null,
            'user_rcc'                 => null,
        ];
    }

    public function admin(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => User::ADMIN,   // administrator
            ];
        });
    }

    public function guestReadOnly(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_type' => User::CLIENT,   // guest_read_only
            ];
        });
    }
}
