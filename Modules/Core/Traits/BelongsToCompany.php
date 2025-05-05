<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Automatically scopes models to the currently authenticated user’s company,
 * and injects the company_id on creation.
 */
trait BelongsToCompany
{
    /**
     * Scope a query to only the current company.
     */
    public function scopeForCompany(Builder $query, $companyId = null): Builder
    {
        $companyId = $companyId ?: static::getCurrentCompanyId();

        return $query->where(
            $query->getModel()->getTable() . '.company_id',
            $companyId
        );
    }

    /**
     * Relationship back to the Company model.
     */
    public function company()
    {
        return $this->belongsTo(\Modules\Core\Models\Company::class);
    }

    /**
     * Determine the current company ID for the authenticated user.
     */
    protected static function getCurrentCompanyId(): ?int
    {
        $user = Auth::user();
        if ( ! $user) {
            return null;
        }

        // Get current company ID from session
        if (session()->has('current_company_id')) {
            return session('current_company_id');
        }

        // If not in session, fallback to the first company in the user's many-to-many relation
        $company = $user->companies()->first();

        if ( ! $company) {
            // Log or handle error if no company is found for the user
            Log::warning("No company found for user with ID {$user->id}");

            return null; // Or throw exception if needed
        }

        return $company->id;
    }

    /**
     * Boot the trait: add global scope and set company_id on create.
     */
    protected static function bootBelongsToCompany(): void
    {
        static::creating(function ($model): void {
            if (isset($model->company_id) && empty($model->company_id)) {
                $model->company_id = static::getCurrentCompanyId();
            }
        });

        static::addGlobalScope('company_id', function (Builder $builder): void {
            if (Auth::check()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.company_id',
                    static::getCurrentCompanyId()
                );
            }
        });
    }
}
