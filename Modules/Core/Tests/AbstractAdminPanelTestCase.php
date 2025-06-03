<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Core\Models\User;

abstract class AbstractAdminPanelTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        filament()->setCurrentPanel(filament()->getPanel('admin'));
        $this->superAdmin = User::factory()->create();
        // Assign super-admin role or permissions if applicable
        // $this->superAdmin->assignRole('super-admin');

        // Optional: Set current company context
        session(['current_company_id' => $this->superAdmin->getCurrentCompanyId()]);

        $this->withoutExceptionHandling();
    }

    protected function superAdmin(): User
    {
        return $this->superAdmin;
    }
}
