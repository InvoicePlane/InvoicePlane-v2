<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Http\RedirectResponse;
use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Responses\LoginResponse;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[CoversClass(LoginResponse::class)]
class LoginResponseTest extends AbstractAdminPanelTestCase
{
    // region helpers

    private function makeElevatedUser(): User
    {
        Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole(UserRole::SUPER_ADMIN->value);

        return $user;
    }

    private function makeRegularUser(Company $company): User
    {
        Role::query()->firstOrCreate(['name' => UserRole::CUSTOMER->value, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole(UserRole::CUSTOMER->value);
        $user->companies()->attach($company);

        return $user;
    }

    private function dispatchResponse(): RedirectResponse
    {
        return (new LoginResponse())->toResponse(request());
    }

    // endregion

    // region company selection

    /**
     * Regression test for the original bug: Company::query()->first() returns whatever
     * company has the lowest DB id, which is not necessarily ivplv2. If the first company
     * happened to have search_code='admin', the redirect URL became /admin/dashboard which
     * hit the admin panel instead of the company panel, producing /admin/login? silently.
     */
    #[Test]
    #[Group('login')]
    public function it_redirects_elevated_users_to_the_default_company_not_the_first_by_id(): void
    {
        /* Arrange — setUp already created $this->company (lower ID, random code);
           create ivplv2 after so it gets a higher ID and first() would skip it */
        $ivplv2 = Company::factory()->create(['search_code' => 'ivplv2']);
        $user   = $this->makeElevatedUser();
        $this->actingAs($user);

        /* Act */
        $response = $this->dispatchResponse();

        /* Assert — must land on ivplv2, never on the first-by-id company */
        $this->assertEquals(
            route('filament.company.pages.dashboard', ['tenant' => 'ivplv2']),
            $response->headers->get('Location'),
        );
    }

    #[Test]
    #[Group('login')]
    public function it_falls_back_to_first_company_when_default_code_does_not_exist(): void
    {
        /* Arrange — only $this->company exists, no ivplv2 in the database */
        $user = $this->makeElevatedUser();
        $this->actingAs($user);

        /* Act */
        $response = $this->dispatchResponse();

        /* Assert — falls back gracefully to the only company available */
        $this->assertEquals(
            route('filament.company.pages.dashboard', ['tenant' => strtolower($this->company->search_code)]),
            $response->headers->get('Location'),
        );
    }

    #[Test]
    #[Group('login')]
    public function it_redirects_regular_users_to_their_attached_company_not_ivplv2(): void
    {
        /* Arrange — regular user has their own company, ivplv2 also exists */
        Company::factory()->create(['search_code' => 'ivplv2']);
        $ownCompany = Company::factory()->create(['search_code' => 'myco1']);
        $user       = $this->makeRegularUser($ownCompany);
        $this->actingAs($user);

        /* Act */
        $response = $this->dispatchResponse();

        /* Assert — regular users land on their own company, not the global default */
        $this->assertEquals(
            route('filament.company.pages.dashboard', ['tenant' => 'myco1']),
            $response->headers->get('Location'),
        );
    }

    // endregion

    // region session

    #[Test]
    #[Group('login')]
    public function it_sets_session_current_company_id_for_elevated_users(): void
    {
        /* Arrange */
        $ivplv2 = Company::factory()->create(['search_code' => 'ivplv2']);
        $user   = $this->makeElevatedUser();
        $this->actingAs($user);

        /* Act */
        $this->dispatchResponse();

        /* Assert */
        $this->assertEquals($ivplv2->id, session('current_company_id'));
    }

    #[Test]
    #[Group('login')]
    public function it_sets_session_current_company_id_for_regular_users(): void
    {
        /* Arrange */
        $company = Company::factory()->create(['search_code' => 'myco2']);
        $user    = $this->makeRegularUser($company);
        $this->actingAs($user);

        /* Act */
        $this->dispatchResponse();

        /* Assert */
        $this->assertEquals($company->id, session('current_company_id'));
    }

    // endregion

    // region abort conditions

    #[Test]
    #[Group('login')]
    public function it_aborts_with_500_when_no_company_exists_at_all(): void
    {
        /* Arrange — remove all companies (setUp creates one; no FK records exist yet) */
        Company::query()->delete();
        $user = $this->makeElevatedUser();
        $this->actingAs($user);

        /* Assert */
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Fallback company not found.');

        /* Act */
        $this->dispatchResponse();
    }

    #[Test]
    #[Group('login')]
    public function it_aborts_with_500_when_regular_user_has_no_company_attached(): void
    {
        /* Arrange — user exists but is attached to no company */
        Role::query()->firstOrCreate(['name' => UserRole::CUSTOMER->value, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole(UserRole::CUSTOMER->value);
        $this->actingAs($user);

        /* Assert */
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('No company found for this user.');

        /* Act */
        $this->dispatchResponse();
    }

    // endregion
}
