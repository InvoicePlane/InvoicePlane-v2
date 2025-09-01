<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

abstract class AbstractAdminPanelTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        filament()->setCurrentPanel(filament()->getPanel('admin'));

        $this->company = Company::factory()->create();

        $this->superAdmin = User::factory()->create();

        session(['current_company_id' => $this->company->id]);

        $this->withoutExceptionHandling();
    }

    protected function superAdmin(): User
    {
        return $this->superAdmin;
    }
}
