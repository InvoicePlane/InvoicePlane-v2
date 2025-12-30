<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Modules\Core\Enums\UserRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleHasPermissionsSeeder extends Seeder
{
    public function run(?int $companyId = null): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Log::info('Syncing role permissions...');

        $roles = Role::query()->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->with('permissions')
            ->get();

        $allPermissions = Permission::query()->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('name')
            ->toArray();

        $rolesUpdated = 0;

        foreach ($roles as $role) {
            $currentPermissions = $role->permissions->pluck('name')->toArray();
            $defaultPermissions = $this->getDefaultPermissionsForRole($role->name);

            if ($role->name === UserRole::SUPER_ADMIN->value) {
                $newPermissions = $allPermissions;
            } else {
                $newPermissions = array_intersect($defaultPermissions, $allPermissions);

                $customPermissions = array_diff($currentPermissions, $defaultPermissions);
                $newPermissions    = array_unique(array_merge($newPermissions, $customPermissions));
            }

            if (count(array_diff($newPermissions, $currentPermissions)) > 0
                || count(array_diff($currentPermissions, $newPermissions)) > 0) {
                $role->syncPermissions($newPermissions);
                $rolesUpdated++;

                Log::info(trans('ip.role_permissions_updated', [
                    'count' => count($newPermissions),
                    'role'  => $role->display_name ?: $role->name,
                ]));
            }
        }

        Log::info(trans('ip.roles_sync_complete', [
            'updated' => $rolesUpdated,
            'total'   => $roles->count(),
        ]));
    }

    protected function getDefaultPermissionsForRole(string $roleName): array
    {
        $rolesSeeder = new RolesSeeder();
        $roles       = $rolesSeeder->getDefaultRolePermissions();

        if (isset($roles[$roleName])) {
            return $roles[$roleName]['permissions'] ?? [];
        }

        return [];
    }
}
