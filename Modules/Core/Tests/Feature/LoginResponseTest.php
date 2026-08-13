<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Http\RedirectResponse;
use Modules\Core\Filament\Responses\LoginResponse;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[CoversClass(LoginResponse::class)]
class LoginResponseTest extends AbstractAdminPanelTestCase
{
    // endregion

    // region redirect destination

    #[Test]
    #[Group('login')]
    public function it_redirects_to_ivplv2_when_user_is_attached_to_it(): void
    {
        $ivplv2 = Company::factory()->create(['search_code' => 'ivplv2']);
        $user   = $this->makeUser($ivplv2);
        $this->actingAs($user);

        $response = $this->dispatchResponse();

        $this->assertEquals(
            route('filament.company.pages.dashboard', ['tenant' => 'ivplv2']),
            $response->headers->get('Location'),
        );
    }

    #[Test]
    #[Group('login')]
    public function it_redirects_to_ivplv2_even_when_user_has_multiple_companies(): void
    {
        $other  = Company::factory()->create(['search_code' => 'other1']);
        $ivplv2 = Company::factory()->create(['search_code' => 'ivplv2']);
        $user   = $this->makeUser($other, $ivplv2);
        $this->actingAs($user);

        $response = $this->dispatchResponse();

        $this->assertEquals(
            route('filament.company.pages.dashboard', ['tenant' => 'ivplv2']),
            $response->headers->get('Location'),
        );
    }

    #[Test]
    #[Group('login')]
    public function it_falls_back_to_first_attached_company_when_not_attached_to_ivplv2(): void
    {
        /* ivplv2 exists in DB but user is NOT attached to it */
        Company::factory()->create(['search_code' => 'ivplv2']);
        $ownCompany = Company::factory()->create(['search_code' => 'myco1']);
        $user       = $this->makeUser($ownCompany);
        $this->actingAs($user);

        $response = $this->dispatchResponse();

        $this->assertEquals(
            route('filament.company.pages.dashboard', ['tenant' => 'myco1']),
            $response->headers->get('Location'),
        );
    }

    /**
     * Regression: the old elevated-user code path used Company::query()->first() which
     * redirected to ivplv2 based solely on DB presence, bypassing company_user membership.
     * The new logic respects the user's actual attachments only.
     */
    #[Test]
    #[Group('login')]
    public function it_does_not_redirect_to_ivplv2_based_solely_on_db_presence(): void
    {
        Company::factory()->create(['search_code' => 'ivplv2']); // in DB, user NOT attached
        $ownCompany = Company::factory()->create(['search_code' => 'myco2']);
        $user       = $this->makeUser($ownCompany);
        $this->actingAs($user);

        $response = $this->dispatchResponse();

        $this->assertNotEquals(
            route('filament.company.pages.dashboard', ['tenant' => 'ivplv2']),
            $response->headers->get('Location'),
        );
    }

    // endregion

    // region session

    #[Test]
    #[Group('login')]
    public function it_sets_session_current_company_id(): void
    {
        $company = Company::factory()->create(['search_code' => 'myco3']);
        $user    = $this->makeUser($company);
        $this->actingAs($user);

        $this->dispatchResponse();

        $this->assertEquals($company->id, session('current_company_id'));
    }

    // endregion

    // region abort conditions

    #[Test]
    #[Group('login')]
    public function it_aborts_with_403_when_user_has_no_company_attached(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('No company found for your account. Please contact an administrator.');

        $this->dispatchResponse();
    }
    // region helpers

    private function makeUser(Company ...$companies): User
    {
        /** @var User $user */
        $user = User::factory()->create();
        foreach ($companies as $company) {
            $user->companies()->attach($company);
        }

        return $user;
    }

    private function dispatchResponse(): RedirectResponse
    {
        /** @var RedirectResponse $response */
        $response = (new LoginResponse())->toResponse(request());

        return $response;
    }

    // endregion
}
