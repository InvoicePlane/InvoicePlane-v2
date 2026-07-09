<?php

namespace Modules\Core\Services;

use Modules\Core\Enums\Permission as PermissionEnum;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesService extends BaseService
{
    public function model(): string
    {
        return Role::class;
    }

    public function syncPermissionsFromMatrix(array $matrix, User $editor): void
    {
        foreach (Role::all() as $role) {
            if ($role->name === UserRole::SUPER_ADMIN->value) {
                continue;
            }

            $toSync     = [];
            $alreadyHas = $role->permissions->pluck('name')->flip();

            foreach (PermissionEnum::cases() as $perm) {
                if ( ! ($matrix[$role->name][$perm->value] ?? false)) {
                    continue;
                }

                $canGrant       = $editor->isSuperAdmin() || $editor->can($perm->value);
                $alreadyGranted = isset($alreadyHas[$perm->value]);

                // Preserve already-assigned permissions; block only newly checked ones the editor can't grant
                if ($canGrant || $alreadyGranted) {
                    $toSync[] = $perm->value;
                }
            }

            $role->syncPermissions($toSync);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
