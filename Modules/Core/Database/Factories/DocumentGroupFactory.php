<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Enums\DocumentGroupType;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;

/**
 * @extends Factory<DocumentGroup>
 */
class DocumentGroupFactory extends Factory
{
    protected $model = DocumentGroup::class;

    public function definition(): array
    {
        $company   = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();
        $groupType = $this->faker->randomElement(DocumentGroupType::cases());

        // Generate a unique name for the document group
        $name = $groupType->label();
        if ($this->faker->boolean(30)) {
            $name .= ' ' . $this->faker->randomElement(['Standard', 'Primary', 'Secondary', 'Backup']);
        }

        return [
            'company_id'              => $company->id,
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
