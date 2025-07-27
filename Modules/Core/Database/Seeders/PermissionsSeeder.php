<?php

namespace Modules\Core\Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends \Modules\Core\Database\Seeders\AbstractSeeder
{
    public array $basicActions = [
        'view', 'create', 'edit', 'delete',
    ];

    public array $specialPermissions = [
        'customers' => ['manage'],
        'expenses'  => ['approve', 'reject'],
        'invoices'  => ['download', 'duplicate', 'email', 'mark-paid', 'mark-sent', 'print'],
        'payments'  => ['email', 'refund'],
        'products'  => ['export', 'import'],
        'projects'  => ['manage'],
        'quotes'    => ['approve', 'convert-to-invoice', 'download', 'duplicate', 'email', 'mark-sent', 'print', 'reject'],
        'reports'   => ['export', 'manage', 'print'],
        'settings'  => ['manage'],
        'users'     => ['impersonate'],
    ];

    public array $resources = [
        'companies', 'contacts', 'expenses', 'email-templates', 'invoices', 'payments', 'permissions', 'products',
        'projects', 'quotes', 'relations', 'reports', 'roles', 'settings', 'tasks', 'tax-rates', 'users',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [];

        foreach ($this->resources as $resource) {
            foreach ($this->basicActions as $action) {
                $permissions[] = "{$action}-{$resource}";
            }

            // Add special permissions if they exist for this resource
            if (isset($this->specialPermissions[$resource])) {
                foreach ($this->specialPermissions[$resource] as $special) {
                    $permissions[] = "{$special}-{$resource}";
                }
            }
        }

        $permissions = array_merge($permissions, [
            'view-dashboard',
            'manage-company-settings',
            'import',
            'export',
            'backup',
            'restore',
        ]);

        $existingPermissions = Permission::whereIn('name', $permissions)
            ->pluck('name')
            ->toArray();

        $newPermissions = array_diff($permissions, $existingPermissions);

        foreach ($newPermissions as $permission) {
            Permission::create([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info(trans('ip.permissions_updated', [
            'count' => count($permissions),
        ]));
    }
}
