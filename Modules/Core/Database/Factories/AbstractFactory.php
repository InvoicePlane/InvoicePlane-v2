<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;

abstract class AbstractFactory extends Factory
{
    protected function resolveCompanyId(array $attributes = []): ?int
    {
        dump('attributes:', $attributes);
        dump('$this->company:', $this->company ?? null);
        dump('$this->attributes:', $this->attributes ?? null);

        return $attributes['company_id']
            ?? $this->company?->id
            ?? $this->attributes['company_id'] ?? null;
    }

    protected function resolveCompany()
    {
        $companyId = $this->company?->id;

        return $this->company ?? Company::query()->find($companyId);
    }

    protected function resolveForeignKey($relatedClass, $companyId = null)
    {
        if (app()->runningUnitTests()) {
            return $relatedClass::query()->where('company_id', $companyId)
                ->inRandomOrder()
                ->first()?->id
                ?? $relatedClass::factory();
        }
    }
}
