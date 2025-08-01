<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\CustomField;

/**
 * @extends Factory<CustomField>
 */
class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $company   = Company::query()->find($companyId);

        return [
            'company_id'         => $company->id,
            'fieldable_type'     => fake()->word,
            'custom_field_label' => fake()->optional()->word,
            'field_type'         => fake()->word,
            'field_order'        => fake()->word,
        ];
    }
}
