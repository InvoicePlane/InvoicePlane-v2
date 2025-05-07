<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Database\Factories\DocumentGroupFactory;

use Modules\Core\Enums\DocumentGroupType;

use Modules\Core\Models\Company;

use Modules\Core\Models\DocumentGroup;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentGroupFactory extends Factory
{
    protected $model = DocumentGroup::class;

    public function definition(): array
    {
        $groupType = $this->faker->randomElement(DocumentGroupType::cases());

        return [
            'company_id' => Company::query()->inRandomOrder()->first()->id,
            'type'       => $groupType->value,
            'name'       => $groupType->label(),
            'left_pad'   => $groupType->prefix(),
            'format'     => $this->faker->optional()->numerify($groupType->prefix() . '-#####'),
            'next_id'    => 1,
        ];
    }
}
