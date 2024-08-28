<?php

namespace Modules\Clients\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Models\Client;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'client_date_created'  => $this->faker->dateTimeBetween('-3 years', '-2 days'),
            'client_date_modified' => $this->faker->dateTimeBetween('-3 years', '-2 days'),
            'client_name'          => $this->faker->company,
            'client_address_1'     => $this->faker->streetAddress,
            'client_address_2'     => null,
            'client_city'          => $this->faker->city,
            'client_state'         => null,
            'client_zip'           => $this->faker->postcode,
            'client_country'       => $this->faker->countryCode,
            'client_phone'         => $this->faker->phoneNumber,
            'client_fax'           => $this->faker->phoneNumber,
            'client_mobile'        => $this->faker->phoneNumber,
            'client_email'         => $this->faker->email,
            'client_web'           => $this->faker->url,
            'client_vat_id'        => $this->faker->numerify('##########'),
            'client_tax_code'      => $this->faker->numerify('###-###-####'),
            'client_language'      => $this->faker->languageCode(),
            'client_active'        => $this->faker->boolean(90),
            'client_surname'       => null,
            'client_avs'           => null,
            'client_insurednumber' => $this->faker->numerify('##########'),
            'client_veka'          => null,
            'client_birthdate'     => null,
            'client_gender'        => $this->faker->boolean(45),
        ];
    }

    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'client_active' => false,
            ];
        });
    }
}
