<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Enums\UserRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleHasPermissionsSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public function run(?int $companyId = null): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Syncing role permissions...');

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

            // For super admin, sync all permissions
            if ($role->name === UserRole::SUPER_ADMIN->value) {
                $newPermissions = $allPermissions;
            } else {
                // Get the default permissions that exist in the database
                $newPermissions = array_intersect($defaultPermissions, $allPermissions);

                // Preserve any custom permissions that aren't in the defaults
                $customPermissions = array_diff($currentPermissions, $defaultPermissions);
                $newPermissions    = array_unique(array_merge($newPermissions, $customPermissions));
            }

            // Only update if permissions have changed
            if (count(array_diff($newPermissions, $currentPermissions)) > 0 ||
                count(array_diff($currentPermissions, $newPermissions)) > 0) {
                $role->syncPermissions($newPermissions);
                $rolesUpdated++;

                $this->command->info(trans('ip.role_permissions_updated', [
                    'count' => count($newPermissions),
                    'role'  => $role->display_name ?: $role->name,
                ]));
            }
        }

        $this->command->info(trans('ip.roles_sync_complete', [
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
