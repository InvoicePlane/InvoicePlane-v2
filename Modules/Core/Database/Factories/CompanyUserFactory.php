<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\CompanyUser;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class CompanyUserFactory extends AbstractFactory
{
    protected $model = CompanyUser::class;

    public function definition(): array
    {
        $company = $this->resolveCompany() ?? Company::factory()->create();

        return [
            'company_id' => $company->id,
            'user_id'    => User::query()->inRandomOrder()->first()->id,
        ];
    }
}
