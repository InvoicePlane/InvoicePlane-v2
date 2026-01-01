<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

abstract class AbstractAdminPanelTestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected Company $company;

    protected ?User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        filament()->setCurrentPanel(filament()->getPanel('admin'));

        /** @var Company $company */
        $company       = Company::factory()->create();
        $this->company = $company;

        /** @var User $superAdmin */
        $superAdmin       = User::factory()->create();
        $this->superAdmin = $superAdmin;

        session(['current_company_id' => $this->company->id]);

        $this->withoutExceptionHandling();
    }

    protected function superAdmin(): User
    {
        return $this->superAdmin;
    }
}
