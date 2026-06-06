<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;

abstract class AbstractFactory extends Factory
{
    protected function resolveCompanyId(array $attributes = []): ?int
    {
        return $attributes['company_id']
            ?? $this->company?->id
            ?? $this->attributes['company_id'] ?? null;
    }

    protected function resolveCompany(array $attributes = []): ?Company
    {
        $companyId = $this->resolveCompanyId($attributes);

        return $companyId ? Company::query()->find($companyId) : null;
    }

    protected function resolveForeignKey($relatedClass, $companyId = null)
    {
        if (app()->runningUnitTests() && $companyId !== null) {
            return $relatedClass::query()->where('company_id', $companyId)
                ->inRandomOrder()
                ->first()?->id
                ?? $relatedClass::factory();
        }

        return $relatedClass::factory();
    }
}
