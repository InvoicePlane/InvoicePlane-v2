<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\CustomField;

/**
 * @extends Factory<\Modules\Core\Models\CustomField>
 */
class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        return [
            'company_id'         => $company->id,
            'fieldable_type'     => fake()->word,
            'custom_field_label' => fake()->optional()->word,
            'field_type'         => fake()->word,
            'field_order'        => fake()->word,
        ];
    }
}
