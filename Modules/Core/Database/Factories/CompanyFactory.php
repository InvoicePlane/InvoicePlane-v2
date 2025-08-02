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

        // Get a random logo from a set of sample company logos
        $logos = [
            'logos/company1.png',
            'logos/company2.png',
            'logos/company3.png',
            null, // 25% chance of no logo
        ];

        // Available templates in the system
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
            'vat_number'       => $this->faker->optional(0.8)->regexify('^(BE|NL|DE|FR|LU)\d{9}$'), // 80% chance of having a VAT number
            'id_number'        => $this->faker->optional(0.7)->numerify('#########'), // 70% chance of having an ID number
            'coc_number'       => $this->faker->optional(0.9)->numerify('#########'), // 90% chance of having a COC number
            'logo'             => $this->faker->optional(0.75)->randomElement($logos), // 75% chance of having a logo
            'quote_template'   => $this->faker->randomElement($templates),
            'invoice_template' => $this->faker->randomElement($templates),
        ];
    }
}
