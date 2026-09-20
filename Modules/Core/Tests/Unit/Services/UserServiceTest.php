<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Services\UserService;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserService::class)]
class UserServiceTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UserService::class);
    }

    #[Test]
    public function it_allows_a_user_to_switch_to_a_company_they_belong_to(): void
    {
        /* Arrange */
        $user = User::factory()->withCompany(['search_code' => 'MEMBER'])->create();

        /** @var Company $company */
        $company = $user->companies()->first();

        /* Act & Assert */
        $this->service->assertBelongsToCompany($user, $company);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_refuses_to_switch_to_a_company_the_user_does_not_belong_to(): void
    {
        /* Arrange */
        $user           = User::factory()->withCompany(['search_code' => 'MEMBER'])->create();
        $foreignCompany = Company::factory()->create(['search_code' => 'FOREIGN']);

        /* Assert */
        $this->expectException(AuthorizationException::class);

        /* Act */
        $this->service->assertBelongsToCompany($user, $foreignCompany);
    }
}
