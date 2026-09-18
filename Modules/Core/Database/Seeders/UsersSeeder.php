<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Enums\UserRole;
use Modules\Core\Models\User;

class UsersSeeder extends AbstractSeeder
{
    protected string $label = 'Users';

    protected int $defaultCount = 15;

    protected function buildOne(): void
    {
        $user = User::factory()->create();
        $user->companies()->attach($this->companyId);
        $role = collect(UserRole::nonAdmin())->random();
        $user->assignRole($role);
    }
}
