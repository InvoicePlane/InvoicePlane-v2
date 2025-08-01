<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Models\User;

class UsersSeeder extends AbstractSeeder
{
    protected string $label        = 'Users';
    protected int    $defaultCount = 3;

    protected function buildOne(): void
    {
        $user = User::factory()
            ->state(['company_id' => $this->companyId])
            ->create();

        $user->companies()->attach($this->companyId);
    }
}
