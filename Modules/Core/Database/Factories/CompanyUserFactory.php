<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Models\CompanyUser;
use Modules\Core\Models\User;

class CompanyUserFactory extends AbstractFactory
{
    protected $model = CompanyUser::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        return [
            'user_id' => User::query()->inRandomOrder()->first()->id,
        ];
    }
}
