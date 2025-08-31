<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\CustomField;

class CustomFieldFactory extends AbstractFactory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        return [
            'fieldable_type'     => fake()->word,
            'custom_field_label' => fake()->optional()->word,
            'field_type'         => fake()->word,
            'field_order'        => fake()->word,
        ];
    }
}
