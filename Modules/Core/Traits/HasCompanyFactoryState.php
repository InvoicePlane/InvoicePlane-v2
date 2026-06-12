<?php

namespace Modules\Core\Traits;

use Modules\Core\Models\Company;
use Modules\Core\Models\User;

trait HasCompanyFactoryState
{
    public function withCompany(array $companyInfo = []): self
    {
        return $this->afterCreating(function (User $user) use ($companyInfo): void {
            $company = Company::factory()->create($companyInfo);
            $user->companies()->attach($company->id);
        });
    }
}
