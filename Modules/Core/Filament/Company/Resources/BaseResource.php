<?php

namespace Modules\Core\Filament\Company\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Enums\UserRole;

abstract class BaseResource extends Resource
{
    /**
     * Returns the base Eloquent query for the resource, removing global scopes for elevated users.
     */
    public static function getEloquentQuery(): Builder
    {
        $user  = auth()->user();
        $model = static::getModel();

        // Start with a fresh query with all global scopes
        $query = $model::query();

        // For non-elevated users, just return the scoped query
        if ( ! $user || ! method_exists($user, 'hasRole')) {
            return $query;
        }

        // Check if user has any elevated role
        $isElevated = false;
        foreach (UserRole::elevated() as $role) {
            if ($user->hasRole($role)) {
                $isElevated = true;
                break;
            }
        }

        // If not elevated, return the scoped query
        if ( ! $isElevated) {
            return $query;
        }

        // For elevated users, we need to ensure the tenant scope is applied
        // First, get all global scopes
        $modelInstance = new $model();
        $globalScopes  = $modelInstance->getGlobalScopes();

        // Find the tenant scope if it exists
        $tenantScope = null;
        foreach ($globalScopes as $scopeName => $scope) {
            if (str_contains($scopeName, 'TenantScope')) {
                $tenantScope = $scope;
                break;
            }
        }

        // Remove all global scopes
        //$query = $model::withoutGlobalScopes();

        // Re-apply the tenant scope if it exists
        if ($tenantScope) {
            $query->withGlobalScope('TenantScope', $tenantScope);
        }

        return $query;
    }
}
