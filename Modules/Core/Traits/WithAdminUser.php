<?php

namespace Modules\Core\Traits;

use Modules\Core\Traits\WithAdminUser;

use Modules\Core\Models\User;

use Modules\Core\Models\User;

trait WithAdminUser
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Future-proofing for Filament Shield
        // $this->user->assignRole('super-admin');
    }
}
