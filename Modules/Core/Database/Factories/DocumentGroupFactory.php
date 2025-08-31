<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Models\DocumentGroup;

class DocumentGroupFactory extends AbstractFactory
{
    protected $model = DocumentGroup::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();
        $groupType = $this->faker->randomElement(DocumentGroupType::cases());

        $name = $groupType->label();
        if ($this->faker->boolean(30)) {
            $name .= ' ' . $this->faker->randomElement(['Standard', 'Primary', 'Secondary', 'Backup']);
        }

        return [
            'type'                    => $groupType->value,
            'group_identifier_format' => $groupType->prefix() . '-{YEAR}-{MONTH}-{ID}',
            'name'                    => $name,
            'left_pad'                => $this->faker->numberBetween(3, 6),
            'format'                  => $groupType->prefix() . '-{YEAR}-{MONTH}-{ID}',
            'next_id'                 => 1,
            'reset_number'            => $this->faker->randomElement([0, 1]),
            'last_id'                 => 0,
            'last_year'               => now()->year,
            'last_month'              => now()->month,
            'last_week'               => now()->week,
        ];
    }
}
