<?php

namespace Modules\Core\Traits;

use Modules\Core\Traits\WithUserCompany;

use Modules\Core\Models\User;


trait WithUserCompany
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->withCompany()->create();
        session(['current_company_id' => $this->user->company_id]);
    }
}
