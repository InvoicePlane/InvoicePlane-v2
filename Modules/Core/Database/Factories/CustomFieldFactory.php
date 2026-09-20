<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\Company;
use Modules\Core\Models\CustomField;

class CustomFieldFactory extends AbstractFactory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        $company = $this->resolveCompany() ?? Company::factory()->create();

        return [
            'company_id'         => $company->id,
            'fieldable_type'     => \Modules\Clients\Models\Relation::class,
            'custom_field_label' => fake()->optional()->word,
            'field_type'         => 'TEXT',
            'field_order'        => fake()->numberBetween(0, 20),
        ];
    }
}
