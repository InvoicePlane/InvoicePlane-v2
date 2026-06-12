<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Company;

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
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Determine the current company ID for the authenticated user.
     */
    protected static function getCurrentCompanyId(): ?int
    {
        $user = Auth::user();
        if ( ! $user) {
            Log::debug('No authenticated user, company ID not set');

            return null;
        }

        $companyId = null;
        $source    = null;

        // 1. Check Filament tenant context first
        if (function_exists('filament') && $tenant = filament()->getTenant()) {
            $companyId = $tenant->id;
            $source    = 'filament_tenant';
            if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                Log::debug(sprintf(
                    'Using company ID %d from Filament tenant context for user %d',
                    $companyId,
                    $user->id
                ));
            }
        }
        // 2. Check session
        elseif (session()?->has('current_company_id')) {
            $companyId = session('current_company_id');
            $source    = 'session';
            if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                Log::debug(sprintf(
                    'Using company ID %d from session for user %d',
                    $companyId,
                    $user->id
                ));
            }
        }
        // 3. Fallback to first company for user
        else {
            $company = $user->companies()->first();
            if ($company) {
                $companyId = $company->id;
                $source    = 'user_companies';
                if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                    Log::debug(sprintf(
                        'Using company ID %d from user\'s first company for user %d',
                        $companyId,
                        $user->id
                    ));
                }
            } else {
                if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                    Log::warning(sprintf(
                        'No company found for user ID %d',
                        $user->id
                    ));
                }

                return null;
            }
        }

        if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
            Log::debug(sprintf(
                'Selected company ID %d (source: %s) for user %d',
                $companyId,
                $source,
                $user->id
            ));
        }

        return $companyId;
    }

    /**
     * Boot the trait: add global scope and set company_id on create.
     */
    protected static function bootBelongsToCompany(): void
    {
        static::creating(function ($model): void {
            if (isset($model->company_id) && empty($model->company_id)) {
                $companyId         = static::getCurrentCompanyId();
                $model->company_id = $companyId;
                if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                    Log::debug(sprintf(
                        'Setting company_id to %s for new %s model',
                        $companyId ?? 'NULL',
                        get_class($model)
                    ));
                }
            }
        });

        static::addGlobalScope('company_id', function (Builder $builder): void {
            $model     = $builder->getModel();
            $companyId = static::getCurrentCompanyId();

            if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                Log::debug(sprintf(
                    'Applying company scope to %s query. Company ID: %s, Authenticated: %s',
                    get_class($model),
                    $companyId ?? 'NULL',
                    Auth::check() ? 'Yes' : 'No'
                ));
            }

            if ($companyId !== null) {
                $table = $model->getTable();
                $builder->where(
                    "{$table}.company_id",
                    $companyId
                );

                if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                    Log::debug(sprintf(
                        'Added WHERE %s.company_id = %d to query',
                        $table,
                        $companyId
                    ));
                }
            } elseif (Auth::check()) {
                if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                    Log::warning('No company ID available for authenticated user, blocking all records');
                }
                $builder->whereRaw('1 = 0');
            } else {
                if (config('app.extreme_logging', env('APP_EXTREME_LOGGING', false))) {
                    Log::debug('No company ID and no authenticated user, scope not applied');
                }
            }
        });
    }
}
