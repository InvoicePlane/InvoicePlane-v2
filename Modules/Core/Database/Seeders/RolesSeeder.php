<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Modules\Core\Enums\UserRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = $this->getDefaultRolePermissions();

        foreach ($roles as $roleKey => $roleData) {
            $role = Role::query()->firstOrCreate(
                ['name' => $roleKey],
                [
                    'name'       => $roleKey,
                    'guard_name' => 'web',
                ]
            );

            if ($roleKey === UserRole::SUPER_ADMIN->value) {
                $permissions = Permission::query()
                    ->pluck('name')
                    ->toArray();
                $role->syncPermissions($permissions);
                continue;
            }

            if (in_array('all', $roleData['permissions'])) {
                $permissions = Permission::query()
                    ->pluck('name')
                    ->toArray();
            } else {
                $permissions = Permission::whereIn('name', $roleData['permissions'])
                    ->pluck('name')
                    ->toArray();
            }

            $role->syncPermissions($permissions);

            Log::info(trans('ip.role_permissions_updated', [
                'role'  => $roleData['name'],
                'count' => count($permissions),
            ]));
        }

        Log::info(trans('ip.roles_updated', [
            'count' => count($roles),
        ]));
    }

    public function getDefaultRolePermissions(): array
    {
        $permissionsSeeder = new PermissionsSeeder();
        $allCrud           = $this->getAllCrudPermissions($permissionsSeeder->resources, $permissionsSeeder->basicActions);
        $allSpecial        = $this->getAllSpecialPermissions($permissionsSeeder->specialPermissions);
        $systemPermissions = $this->getSystemPermissions();
        $allPermissions    = array_merge($allCrud, $allSpecial, $systemPermissions);

        return [
            UserRole::SUPER_ADMIN->value => [
                'name'        => 'Super Admin',
                'permissions' => ['all'],
            ],
            UserRole::ADMIN->value => [
                'name'        => 'Administrator',
                'permissions' => $allPermissions,
            ],
            UserRole::ASSIST->value => [
                'name'        => 'Assist',
                'permissions' => array_merge(
                    array_filter($allCrud, fn ($p) => ! str_starts_with($p, 'delete-')),
                    array_filter($allSpecial, fn ($p) => ! in_array(explode('-', $p)[0], ['delete', 'manage'])),
                    ['view-dashboard']
                ),
            ],
            UserRole::CUSTOMER_ADMIN->value => [
                'name'        => 'Customer Admin',
                'permissions' => array_merge(
                    array_filter($allCrud, fn ($p) => str_starts_with($p, 'view-')),
                    array_filter(
                        $allCrud,
                        fn ($p) => str_starts_with($p, 'create-') || str_starts_with($p, 'edit-')
                    ),
                    array_filter(
                        $allSpecial,
                        fn ($p) => in_array(explode('-', $p)[0], ['download', 'print', 'email', 'export'])
                    ),
                    ['view-dashboard']
                ),
            ],
            UserRole::CUSTOMER->value => [
                'name'        => 'Customer',
                'permissions' => [
                    'view-contacts', 'edit-contacts',
                    'view-invoices', 'download-invoices', 'print-invoices',
                    'view-quotes', 'download-quotes', 'print-quotes',
                    'view-payments',
                    'view-dashboard',
                ],
            ],
        ];
    }

    protected function getSystemPermissions(): array
    {
        return [
            'view-dashboard', 'manage-company-settings', 'import', 'export', 'backup', 'restore',
        ];
    }

    protected function getAllCrudPermissions(array $resources, array $basicActions): array
    {
        $permissions = [];
        foreach ($resources as $resource) {
            foreach ($basicActions as $action) {
                $permissions[] = "{$action}-{$resource}";
            }
        }

        return $permissions;
    }

    protected function getAllSpecialPermissions(array $specialPermissions): array
    {
        $permissions = [];
        foreach ($specialPermissions as $resource => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$action}-{$resource}";
            }
        }

        return $permissions;
    }
}
