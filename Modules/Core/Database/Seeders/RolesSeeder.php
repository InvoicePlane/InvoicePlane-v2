<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Modules\Core\Enums\Permission as PermissionEnum;
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
        $allPermissions = array_column(PermissionEnum::cases(), 'value');

        $customerResources = ['relations', 'contacts', 'invoices', 'quotes', 'payments', 'projects', 'tasks', 'products', 'expenses'];
        $allowedActions    = ['view-', 'create-', 'edit-', 'download-', 'print-', 'email-', 'approve-', 'export-'];

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
                'permissions' => array_values(array_filter(
                    $allPermissions,
                    fn ($p) => ! str_starts_with($p, 'delete-') && ! str_starts_with($p, 'manage-')
                )),
            ],

            UserRole::CUSTOMER_ADMIN->value => [
                'name'        => 'Customer Admin',
                'permissions' => array_values(array_filter(
                    $allPermissions,
                    function ($p) use ($customerResources, $allowedActions) {
                        if ($p === PermissionEnum::VIEW_DASHBOARD->value) {
                            return true;
                        }

                        $isCustomerResource = (bool) array_filter(
                            $customerResources,
                            fn ($r) => str_ends_with($p, '-' . $r)
                        );
                        $isAllowedAction = (bool) array_filter(
                            $allowedActions,
                            fn ($a) => str_starts_with($p, $a)
                        );

                        return $isCustomerResource && $isAllowedAction;
                    }
                )),
            ],
            UserRole::CUSTOMER->value => [
                'name'        => 'Customer',
                'permissions' => [
                    PermissionEnum::VIEW_CONTACTS->value,
                    PermissionEnum::EDIT_CONTACTS->value,
                    PermissionEnum::VIEW_INVOICES->value,
                    PermissionEnum::DOWNLOAD_INVOICES->value,
                    PermissionEnum::PRINT_INVOICES->value,
                    PermissionEnum::VIEW_QUOTES->value,
                    PermissionEnum::DOWNLOAD_QUOTES->value,
                    PermissionEnum::PRINT_QUOTES->value,
                    PermissionEnum::VIEW_PAYMENTS->value,
                    PermissionEnum::VIEW_DASHBOARD->value,
                ],
            ],
        ];
    }
}
