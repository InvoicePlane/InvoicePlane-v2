<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldValue;

class CustomFieldValueFactory extends AbstractFactory
{
    protected $model = CustomFieldValue::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        return [
            'custom_field_id'    => CustomField::query()->inRandomOrder()->first()->id,
            'fieldable_type'     => fake()->word,
            'fieldable_id'       => null,
            'custom_field_value' => null,
        ];
    }
}
