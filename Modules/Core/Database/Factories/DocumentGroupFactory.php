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

        return [
            'company_id'              => $company->id,
            'type'                    => $groupType->value,
            'group_identifier_format' => $groupType->prefix() . '-' . $this->faker->numberBetween(100, 700),
            'name'                    => $groupType->label(),
            'left_pad'                => 1,
            'format'                  => $this->faker->optional()->numerify($groupType->prefix() . '-#####'),
            'next_id'                 => 1,
            'reset_number'            => 1,
            'last_id'                 => 1,
            'last_year'               => 1,
            'last_month'              => 1,
            'last_week'               => 1,
        ];
    }
}
