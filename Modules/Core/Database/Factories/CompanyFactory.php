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
use Modules\Core\Models\Company as CompanyModel;

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

        $logos = [
            'logos/company1.png',
            'logos/company2.png',
            'logos/company3.png',
            null, // 25% chance of no logo
        ];

        $templates = [
            'classic',
            'default',
            'minimal',
            'modern',
        ];

        return [
            'search_code'      => mb_strtolower($this->faker->bothify('?????')),
            'name'             => $companyName,
            'slug'             => Str::slug($companyName),
            'vat_number'       => $this->faker->optional(0.8)->regexify('^(BE|NL|DE|FR|LU)\d{9}$'),
            'id_number'        => $this->faker->optional(0.7)->numerify('#########'),
            'coc_number'       => $this->faker->optional(0.9)->numerify('#########'),
            'logo'             => $this->faker->optional(0.75)->randomElement($logos),
            'quote_template'   => $this->faker->randomElement($templates),
            'invoice_template' => $this->faker->randomElement($templates),
        ];
    }
}
