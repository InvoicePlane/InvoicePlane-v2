<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Pages\Auth\Login;
use Modules\Core\Filament\Responses\LoginResponse;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

#[CoversClass(LoginResponse::class)]
class LoginRedirectTest extends AbstractCompanyPanelTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 00:00:00'));
        filament()->setCurrentPanel(filament()->getPanel('company'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function activeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'is_active'         => true,
            'email_verified_at' => Carbon::now(),
            'password'          => bcrypt('password'),
        ], $overrides));
    }

    private function ivplv2Company(): Company
    {
        return Company::factory()->create([
            'search_code' => 'ivplv2',
            'name'        => 'InvoicePlane Corporation',
            'slug'        => 'invoiceplane-corporation',
        ]);
    }

    private function elevatedRole(string $role): void
    {
        Role::query()->firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }

    # region elevated users

    #[Test]
    #[Group('authentication')]
    #[Group('redirect')]
    public function it_redirects_elevated_user_to_ivplv2_dashboard_after_login(): void
    {
        /* Arrange */
        $this->elevatedRole(UserRole::SUPER_ADMIN->value);
        $this->ivplv2Company();

        $user = $this->activeUser(['email' => 'super@example.com']);
        $user->assignRole(UserRole::SUPER_ADMIN->value);

        /* Act */
        $response = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'super@example.com',
                'password' => 'password',
            ])
            ->call('authenticate');

        /* Assert */
        $response->assertRedirect(
            route('filament.company.pages.dashboard', ['tenant' => 'ivplv2'])
        );
        $this->assertAuthenticated();
    }

    #[Test]
    #[Group('authentication')]
    #[Group('redirect')]
    public function it_redirects_admin_user_to_ivplv2_dashboard_after_login(): void
    {
        /* Arrange */
        $this->elevatedRole(UserRole::ADMIN->value);
        $this->ivplv2Company();

        $user = $this->activeUser(['email' => 'admin@example.com']);
        $user->assignRole(UserRole::ADMIN->value);

        /* Act */
        $response = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'admin@example.com',
                'password' => 'password',
            ])
            ->call('authenticate');

        /* Assert */
        $response->assertRedirect(
            route('filament.company.pages.dashboard', ['tenant' => 'ivplv2'])
        );
    }

    #[Test]
    #[Group('authentication')]
    #[Group('redirect')]
    public function it_falls_back_to_first_company_when_ivplv2_absent_for_elevated_user(): void
    {
        /* Arrange */
        $this->elevatedRole(UserRole::SUPER_ADMIN->value);
        $otherCompany = Company::factory()->create(['search_code' => 'acme']);

        $user = $this->activeUser(['email' => 'super@example.com']);
        $user->assignRole(UserRole::SUPER_ADMIN->value);

        /* Act */
        $response = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'super@example.com',
                'password' => 'password',
            ])
            ->call('authenticate');

        /* Assert */
        $response->assertRedirect(
            route('filament.company.pages.dashboard', ['tenant' => 'acme'])
        );
    }

    # endregion

    # region regular users

    #[Test]
    #[Group('authentication')]
    #[Group('redirect')]
    public function it_redirects_regular_user_to_ivplv2_when_attached_to_it(): void
    {
        /* Arrange */
        $this->elevatedRole(UserRole::CUSTOMER_ADMIN->value);
        $ivplv2 = $this->ivplv2Company();

        $user = $this->activeUser(['email' => 'client@example.com']);
        $user->assignRole(UserRole::CUSTOMER_ADMIN->value);
        $user->companies()->attach($ivplv2->id);

        /* Act */
        $response = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'client@example.com',
                'password' => 'password',
            ])
            ->call('authenticate');

        /* Assert */
        $response->assertRedirect(
            route('filament.company.pages.dashboard', ['tenant' => 'ivplv2'])
        );
        $this->assertAuthenticated();
    }

    #[Test]
    #[Group('authentication')]
    #[Group('redirect')]
    public function it_prefers_ivplv2_over_other_companies_for_regular_user(): void
    {
        /* Arrange */
        $this->elevatedRole(UserRole::CUSTOMER_ADMIN->value);
        $other  = Company::factory()->create(['search_code' => 'acme']);
        $ivplv2 = $this->ivplv2Company();

        $user = $this->activeUser(['email' => 'client@example.com']);
        $user->assignRole(UserRole::CUSTOMER_ADMIN->value);
        // Attach other company first — ivplv2 should still win
        $user->companies()->attach($other->id);
        $user->companies()->attach($ivplv2->id);

        /* Act */
        $response = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'client@example.com',
                'password' => 'password',
            ])
            ->call('authenticate');

        /* Assert */
        $response->assertRedirect(
            route('filament.company.pages.dashboard', ['tenant' => 'ivplv2'])
        );
    }

    #[Test]
    #[Group('authentication')]
    #[Group('redirect')]
    public function it_falls_back_to_first_company_when_regular_user_is_not_attached_to_ivplv2(): void
    {
        /* Arrange */
        $this->elevatedRole(UserRole::CUSTOMER_ADMIN->value);
        $otherCompany = Company::factory()->create(['search_code' => 'acme']);

        $user = $this->activeUser(['email' => 'client@example.com']);
        $user->assignRole(UserRole::CUSTOMER_ADMIN->value);
        $user->companies()->attach($otherCompany->id);

        /* Act */
        $response = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'client@example.com',
                'password' => 'password',
            ])
            ->call('authenticate');

        /* Assert */
        $response->assertRedirect(
            route('filament.company.pages.dashboard', ['tenant' => 'acme'])
        );
    }

    # endregion
}
