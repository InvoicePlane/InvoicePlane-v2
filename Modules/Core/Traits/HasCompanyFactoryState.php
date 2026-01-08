<?php

namespace Modules\Core\Traits;

use Modules\Core\Models\Company;
use Modules\Core\Models\User;

trait HasCompanyFactoryState
{
    public function withCompany(): self
    {
        return $this->afterCreating(function (User $user): void {
            $company = Company::factory()->create();
            $user->companies()->attach($company->id);
        });
    }
}
