<?php

namespace Modules\Core\Database\Factories;

use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldValue;

class CustomFieldValueFactory extends AbstractFactory
{
    protected $model = CustomFieldValue::class;

    public function definition(): array
    {
        $company    = $this->resolveCompany() ?? Company::factory()->create();
        $customField = CustomField::query()->where('company_id', $company->id)->inRandomOrder()->first()
            ?? CustomField::factory()->for($company)->create();
        $fieldable  = Relation::factory()->for($company)->create();

        return [
            'company_id'         => $company->id,
            'custom_field_id'    => $customField->id,
            'fieldable_type'     => $fieldable->getMorphClass(),
            'fieldable_id'       => $fieldable->id,
            'custom_field_value' => fake()->word,
        ];
    }
}
