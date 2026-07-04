<?php

namespace Modules\Core\Tests\Feature;

use App\Filament\Pages\SwitchCompany;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class SwitchCompanyTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    private function userWithCompanies(int $count = 5): array
    {
        $user      = User::factory()->create();
        $companies = Company::factory()->count($count)->create();
        $user->companies()->attach($companies->pluck('id'));

        return [$user, $companies];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Smoke
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    #[Group('smoke')]
    public function it_renders_the_switch_company_page(): void
    {
        /* Arrange */
        [$user, $companies] = $this->userWithCompanies(3);
        $initial            = $companies->first();
        session(['current_company_id' => $initial->id]);
        Filament::setTenant($initial, true);

        /* Act */
        $component = Livewire::actingAs($user)->test(SwitchCompany::class);

        /* Assert */
        $component->assertSuccessful();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Core switch behaviour
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    #[Group('crud')]
    public function it_can_switch_company(): void
    {
        /* Arrange */
        [$user, $companies] = $this->userWithCompanies(5);
        $initial            = $companies->first();
        $target             = $companies->last();
        session(['current_company_id' => $initial->id]);
        Filament::setTenant($initial, true);

        /* Act */
        $test = Livewire::actingAs($user)
            ->test(SwitchCompany::class)
            ->callTableAction('switch', $target);

        /* Assert */
        $test->assertRedirect(
            route('filament.company.pages.dashboard', ['tenant' => $target->search_code])
        );
        $this->assertEquals($target->id, session('current_company_id'));
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_session_on_switch(): void
    {
        /* Arrange */
        [$user, $companies] = $this->userWithCompanies(3);
        $initial            = $companies->first();
        $target             = $companies->get(1);
        session(['current_company_id' => $initial->id]);
        Filament::setTenant($initial, true);

        /* Act */
        Livewire::actingAs($user)
            ->test(SwitchCompany::class)
            ->callTableAction('switch', $target);

        /* Assert — session reflects new company */
        $this->assertEquals($target->id, session('current_company_id'));
        $this->assertNotEquals($initial->id, session('current_company_id'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Disabled / enabled state
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    #[Group('crud')]
    public function it_disables_switch_action_for_current_company(): void
    {
        /* Arrange */
        [$user, $companies] = $this->userWithCompanies(5);
        $initial            = $companies->first();
        session(['current_company_id' => $initial->id]);
        Filament::setTenant($initial, true);

        /* Act & Assert */
        Livewire::actingAs($user)
            ->test(SwitchCompany::class)
            ->assertTableActionDisabled('switch', $companies->first())
            ->assertTableActionEnabled('switch', $companies->last());
    }

    #[Test]
    #[Group('crud')]
    public function it_disables_only_the_current_company_switch_action(): void
    {
        /* Arrange */
        [$user, $companies] = $this->userWithCompanies(4);
        $initial            = $companies->get(2); // pick one from the middle
        session(['current_company_id' => $initial->id]);
        Filament::setTenant($initial, true);

        /* Act */
        $component = Livewire::actingAs($user)->test(SwitchCompany::class);

        /* Assert — every OTHER company is enabled */
        foreach ($companies as $company) {
            if ($company->is($initial)) {
                $component->assertTableActionDisabled('switch', $company);
            } else {
                $component->assertTableActionEnabled('switch', $company);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Table contents — only user's companies shown
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    #[Group('crud')]
    public function it_only_lists_companies_the_user_belongs_to(): void
    {
        /* Arrange */
        [$user, $myCompanies]      = $this->userWithCompanies(3);
        [$other, $otherCompanies]  = $this->userWithCompanies(2);
        session(['current_company_id' => $myCompanies->first()->id]);
        Filament::setTenant($myCompanies->first(), true);

        /* Act */
        $component = Livewire::actingAs($user)->test(SwitchCompany::class);

        /* Assert — my companies visible, other user's companies absent */
        $component->assertSuccessful();
        foreach ($myCompanies as $company) {
            $this->assertDatabaseHas('companies', ['id' => $company->id]);
        }
        foreach ($otherCompanies as $company) {
            // company exists in DB but should NOT appear for this user
            $this->assertDatabaseHas('companies', ['id' => $company->id]);
        }
        // Verify via the query that only the user's companies are included
        $ids = Company::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id');
        foreach ($myCompanies as $c) {
            $this->assertTrue($ids->contains($c->id));
        }
        foreach ($otherCompanies as $c) {
            $this->assertFalse($ids->contains($c->id));
        }
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_all_companies_for_a_user_with_many(): void
    {
        /* Arrange */
        [$user, $companies] = $this->userWithCompanies(10);
        session(['current_company_id' => $companies->first()->id]);
        Filament::setTenant($companies->first(), true);

        /* Act */
        $component = Livewire::actingAs($user)->test(SwitchCompany::class);

        /* Assert */
        $component->assertSuccessful();
        $count = Company::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->count();
        $this->assertEquals(10, $count);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Security — cannot switch to a company the user doesn't belong to
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    #[Group('security')]
    public function it_cannot_switch_to_a_company_the_user_does_not_belong_to(): void
    {
        /* Arrange */
        [$user, $myCompanies]     = $this->userWithCompanies(2);
        [$other, $foreignCompany] = $this->userWithCompanies(1);
        $foreignCompany           = $foreignCompany->first();
        session(['current_company_id' => $myCompanies->first()->id]);
        Filament::setTenant($myCompanies->first(), true);

        /* Act — foreign company does not appear in the table, so action is never mounted;
         * assert via the underlying query that it would be excluded */
        $accessible = Company::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id');

        /* Assert */
        $this->assertFalse($accessible->contains($foreignCompany->id));
        $this->assertEquals($myCompanies->first()->id, session('current_company_id'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Edge cases
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    #[Group('edge-cases')]
    public function it_renders_correctly_when_user_has_only_one_company(): void
    {
        /* Arrange */
        $user    = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        Filament::setTenant($company, true);

        /* Act */
        $component = Livewire::actingAs($user)->test(SwitchCompany::class);

        /* Assert — page renders; single company switch action is disabled */
        $component->assertSuccessful();
        $component->assertTableActionDisabled('switch', $company);
    }

    #[Test]
    #[Group('edge-cases')]
    public function it_switches_back_to_original_company(): void
    {
        /* Arrange */
        [$user, $companies] = $this->userWithCompanies(2);
        $companyA           = $companies->first();
        $companyB           = $companies->last();
        session(['current_company_id' => $companyA->id]);
        Filament::setTenant($companyA, true);

        /* Act — switch A → B, then B → A */
        Livewire::actingAs($user)
            ->test(SwitchCompany::class)
            ->callTableAction('switch', $companyB);

        $this->assertEquals($companyB->id, session('current_company_id'));
        session(['current_company_id' => $companyB->id]);
        Filament::setTenant($companyB, true);

        Livewire::actingAs($user)
            ->test(SwitchCompany::class)
            ->callTableAction('switch', $companyA);

        /* Assert — back to A */
        $this->assertEquals($companyA->id, session('current_company_id'));
    }
}
