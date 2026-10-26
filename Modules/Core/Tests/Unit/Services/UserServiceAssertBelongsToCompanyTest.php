<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Services\UserService;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class UserServiceAssertBelongsToCompanyTest extends AbstractCompanyPanelTestCase
{
    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_user_attached_to_company(): void
    {
        /* Arrange: $this->user belongs to $this->company */
        $this->expectNotToPerformAssertions();

        /* Act */
        $this->userService->assertBelongsToCompany($this->user, $this->company);
    }

    #[Test]
    #[Group('unit')]
    public function it_throws_authorization_exception_for_unattached_company(): void
    {
        /* Arrange */
        $unrelatedCompany = Company::factory()->create();

        /* Assert & Act */
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage(trans('ip.user_not_in_company') ?: 'You do not have access to this company.');

        $this->userService->assertBelongsToCompany($this->user, $unrelatedCompany);
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_elevated_role_to_access_unattached_company(): void
    {
        /* Arrange */
        Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
        $this->user->assignRole(UserRole::SUPER_ADMIN->value);
        $unrelatedCompany = Company::factory()->create();

        $this->expectNotToPerformAssertions();

        /* Act */
        $this->userService->assertBelongsToCompany($this->user, $unrelatedCompany);
    }

    #[Test]
    #[Group('unit')]
    public function it_accepts_integer_company_id(): void
    {
        /* Arrange */
        $this->expectNotToPerformAssertions();

        /* Act */
        $this->userService->assertBelongsToCompany($this->user, $this->company->id);
    }
}
