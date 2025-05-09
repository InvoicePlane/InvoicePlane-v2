<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Core\Models\User;

abstract class AbstractCompanyPanelTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user       = User::factory()->withCompany()->create();
        $currentCompanyId = $this->user->getCurrentCompanyId();
        session(['current_company_id' => $currentCompanyId]);
        $this->withoutExceptionHandling();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
