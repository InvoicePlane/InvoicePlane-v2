<?php

namespace Modules\Core\Filament\Company\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Enums\UserRole;

abstract class BaseResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $user  = auth()->user();
        $model = static::getModel();

        $query = $model::query();

        if ( ! $user || ! method_exists($user, 'hasRole')) {
            return $query;
        }

        $isElevated = false;
        foreach (UserRole::elevated() as $role) {
            if ($user->hasRole($role)) {
                $isElevated = true;
                break;
            }
        }

        if ( ! $isElevated) {
            return $query;
        }

        $modelInstance = new $model();
        $globalScopes  = $modelInstance->getGlobalScopes();

        $tenantScope = null;
        foreach ($globalScopes as $scopeName => $scope) {
            if (str_contains($scopeName, 'TenantScope')) {
                $tenantScope = $scope;
                break;
            }
        }

        if ($tenantScope) {
            $query->withGlobalScope('TenantScope', $tenantScope);
        }

        return $query;
    }
}
