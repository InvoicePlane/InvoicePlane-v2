<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;

class NumberingFactory extends AbstractFactory
{
    protected $model = Numbering::class;

    public function definition(): array
    {
        $companyId     = $this->resolveCompanyId();
        $company       = $this->resolveCompany();
        $numberingType = $this->faker->randomElement(NumberingType::cases());

        $name = $numberingType->label();
        if ($this->faker->boolean(30)) {
            $name .= ' ' . $this->faker->randomElement(['Standard', 'Primary', 'Secondary', 'Custom']);
        }

        return [
            'company_id' => $companyId,
            'type'       => $numberingType->value,
            'name'       => $name,
            'next_id'    => 1,
            'left_pad'   => $this->faker->numberBetween(3, 6),
            'format'     => '{{prefix}}-{{number}}',
            'prefix'     => $numberingType->prefix(),
            'last_id'    => null,
        ];
    }
}
