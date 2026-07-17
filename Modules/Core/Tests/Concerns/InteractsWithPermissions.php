<?php

namespace Modules\Core\Tests\Concerns;

use Modules\Core\Enums\Permission as PermissionEnum;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Shared helper for per-module Filament authorization tests (see #503).
 * Grants exactly the permission(s) under test to $this->user, creating the
 * underlying Spatie Permission record if it doesn't exist yet and busting
 * the permission cache -- required whenever a permission record is created
 * mid-test, not just when assigned.
 */
trait InteractsWithPermissions
{
    protected function grantPermission(PermissionEnum|string ...$permissions): void
    {
        $names = array_map(fn ($p) => $p instanceof PermissionEnum ? $p->value : $p, $permissions);

        foreach ($names as $name) {
            SpatiePermission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->user->givePermissionTo($names);
    }

    protected function revokePermission(PermissionEnum|string ...$permissions): void
    {
        $names = array_map(fn ($p) => $p instanceof PermissionEnum ? $p->value : $p, $permissions);
        $this->user->revokePermissionTo($names);
    }
}
