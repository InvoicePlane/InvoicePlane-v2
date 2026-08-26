<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Modules\Core\Enums\Permission as PermissionEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = array_column(PermissionEnum::cases(), 'value');

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

        Log::info(trans('ip.permissions_updated', [
            'count' => count($permissions),
        ]));
    }
}
