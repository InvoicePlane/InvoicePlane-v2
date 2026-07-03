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

            if ($role->name === UserRole::SUPER_ADMIN->value) {
                $toAdd = array_diff($allPermissions, $currentPermissions);
            } else {
                $defaultPermissions = $this->getDefaultPermissionsForRole($role->name);
                $toAdd              = array_diff(
                    array_intersect($defaultPermissions, $allPermissions),
                    $currentPermissions
                );
            }

            if (count($toAdd) > 0) {
                $role->givePermissionTo($toAdd);
                $rolesUpdated++;

                Log::info('Added ' . count($toAdd) . ' permission(s) to role [' . $role->name . ']: ' . implode(', ', $toAdd));
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
