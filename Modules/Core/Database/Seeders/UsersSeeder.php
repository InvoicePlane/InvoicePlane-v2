<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Enums\UserRole;
use Modules\Core\Models\User;

class UsersSeeder extends AbstractSeeder
{
    protected string $label = 'Users';

    protected int $defaultCount = 3;

    protected function buildOne(): void
    {
        $this->command->info('[DEBUG] Seeding User for companyId: ' . $this->companyId);
        $user = User::factory()->create();
        $user->companies()->attach($this->companyId);
        $roleName = collect(UserRole::nonAdmin())->random();

        $user->assignRole($roleName);
    }
}
