<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\Company;
use Modules\Core\Models\Setting;

class SettingFactory extends AbstractFactory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'setting_key'   => fake()->unique()->word,
            'setting_value' => fake()->word,
            'company_id'    => null,
        ];
    }

    /**
     * State: create a setting scoped to a specific company.
     */
    public function forCompany(Company|int $company): self
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        return $this->state(fn (): array => [
            'company_id'    => $companyId,
            'setting_key'   => fake()->unique()->word,
        ]);
    }
}
