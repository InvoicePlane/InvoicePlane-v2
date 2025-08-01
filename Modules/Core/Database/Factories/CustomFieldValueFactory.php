<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\CustomFieldValue;

/**
 * @extends Factory<CustomFieldValue>
 */
class CustomFieldValueFactory extends Factory
{
    protected $model = CustomFieldValue::class;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $company   = Company::query()->find($companyId);

        return [
            'company_id'         => $company->id,
            'custom_field_id'    => \Modules\Core\Models\CustomField::query()->inRandomOrder()->first()->id,
            'fieldable_type'     => fake()->word,
            'fieldable_id'       => null,
            'custom_field_value' => null,
        ];
    }
}
