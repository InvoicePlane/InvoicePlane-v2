<?php

namespace Modules\Core\Tests\Feature;

use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Modules\Core\Enums\PanelTheme;
use Modules\Core\Filament\Company\Pages\CompanySettings;
use Modules\Core\Http\Middleware\ApplyCompanyTheme;
use Modules\Core\Models\Company;
use Modules\Core\Models\Setting;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PanelTheme::class)]
#[CoversClass(ApplyCompanyTheme::class)]
class PanelThemeSettingTest extends AbstractCompanyPanelTestCase
{
    # region enum
    #[Test]
    #[Group('theme')]
    public function every_case_maps_to_a_stylesheet_that_exists(): void
    {
        /* Assert: a case without a stylesheet would fail Vite manifest lookup
           on every page of the panel. */
        foreach (PanelTheme::cases() as $theme) {
            $this->assertFileExists(
                base_path($theme->viteEntrypoint()),
                $theme->value . ' has no stylesheet',
            );
        }
    }

    #[Test]
    #[Group('theme')]
    public function every_case_is_a_built_vite_entrypoint(): void
    {
        /* Arrange */
        $config = file_get_contents(base_path('vite.config.js'));

        /* Assert: an entrypoint missing from vite.config.js is not in the
           manifest, so selecting it would 500 the panel. */
        foreach (PanelTheme::cases() as $theme) {
            $this->assertStringContainsString(
                "'" . $theme->viteEntrypoint() . "'",
                $config,
                $theme->value . ' is not a Vite entrypoint',
            );
        }
    }

    #[Test]
    #[Group('theme')]
    public function from_value_falls_back_to_the_default_for_unusable_values(): void
    {
        /* Act & Assert */
        $this->assertSame(PanelTheme::default(), PanelTheme::fromValue(null));
        $this->assertSame(PanelTheme::default(), PanelTheme::fromValue(''));
        $this->assertSame(PanelTheme::default(), PanelTheme::fromValue('../../etc/passwd'));
        $this->assertSame(PanelTheme::default(), PanelTheme::fromValue('a-theme-that-was-deleted'));
        $this->assertSame(PanelTheme::NORD, PanelTheme::fromValue('nord'));
    }
    # endregion

    # region settings form
    #[Test]
    #[Group('theme')]
    public function it_persists_the_chosen_theme_for_the_current_company_only(): void
    {
        /* Arrange */
        $other = Company::factory()->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->set('data.' . Setting::KEY_PANEL_THEME, PanelTheme::NORD->value)
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertSame('nord', Setting::getForCompany($this->company->id, Setting::KEY_PANEL_THEME));
        $this->assertNull(Setting::getForCompany($other->id, Setting::KEY_PANEL_THEME, null, true));
    }

    #[Test]
    #[Group('theme')]
    public function it_prefills_the_default_theme_when_the_company_has_never_chosen_one(): void
    {
        /* Act */
        $data = Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->get('data');

        /* Assert */
        $this->assertSame(PanelTheme::default()->value, $data[Setting::KEY_PANEL_THEME] ?? null);
    }

    #[Test]
    #[Group('theme')]
    public function it_prefills_the_stored_theme(): void
    {
        /* Arrange */
        Setting::saveForCompany($this->company->id, Setting::KEY_PANEL_THEME, PanelTheme::REDDIT->value);

        /* Act */
        $data = Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->get('data');

        /* Assert */
        $this->assertSame('reddit', $data[Setting::KEY_PANEL_THEME] ?? null);
    }

    #[Test]
    #[Group('theme')]
    public function changing_the_theme_reloads_the_page_so_the_stylesheet_swaps(): void
    {
        /* Act & Assert: the <link> lives in the document head, which a
           Livewire round-trip cannot rewrite. */
        Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->set('data.' . Setting::KEY_PANEL_THEME, PanelTheme::ORANGE->value)
            ->call('save')
            ->assertRedirect(CompanySettings::getUrl());
    }

    #[Test]
    #[Group('theme')]
    public function saving_without_touching_the_theme_does_not_reload(): void
    {
        /* Act & Assert */
        Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->set('data.' . Setting::KEY_COMPANY_NAME, 'Acme Corp')
            ->call('save')
            ->assertNoRedirect()
            ->assertDispatched('saved');
    }
    # endregion

    # region middleware
    #[Test]
    #[Group('theme')]
    public function the_middleware_points_the_panel_at_the_companys_theme(): void
    {
        /* Arrange */
        $this->actingAs($this->user);
        Setting::saveForCompany($this->company->id, Setting::KEY_PANEL_THEME, PanelTheme::NORD->value);
        Filament::setCurrentPanel('company');

        /* Act */
        (new ApplyCompanyTheme())->handle(Request::create('/'), fn (Request $request) => response(''));

        /* Assert */
        $this->assertSame(
            PanelTheme::NORD->viteEntrypoint(),
            Filament::getCurrentPanel()->getViteTheme(),
        );
    }

    #[Test]
    #[Group('theme')]
    public function a_real_panel_request_runs_the_middleware(): void
    {
        /* Arrange: the unit tests above invoke the middleware directly, which
           proves nothing about whether it is wired into the routing stack. */
        Setting::saveForCompany($this->company->id, Setting::KEY_PANEL_THEME, PanelTheme::NORD->value);

        /* Act */
        $response = $this->actingAs($this->user)->get(
            route('filament.company.pages.dashboard', ['tenant' => 'IVPLV2']),
        );

        /* Assert: the panel is a container singleton, so the instance the
           request mutated is the one still registered afterwards. */
        $response->assertSuccessful();
        $this->assertSame(
            PanelTheme::NORD->viteEntrypoint(),
            Filament::getPanel('company')->getViteTheme(),
        );
    }

    #[Test]
    #[Group('theme')]
    public function the_middleware_falls_back_to_the_default_theme(): void
    {
        /* Arrange: a value no longer backed by a case -- e.g. a theme removed
           after a company had selected it. */
        $this->actingAs($this->user);
        Setting::saveForCompany($this->company->id, Setting::KEY_PANEL_THEME, 'retired-theme');
        Filament::setCurrentPanel('company');

        /* Act */
        (new ApplyCompanyTheme())->handle(Request::create('/'), fn (Request $request) => response(''));

        /* Assert */
        $this->assertSame(
            PanelTheme::default()->viteEntrypoint(),
            Filament::getCurrentPanel()->getViteTheme(),
        );
    }
    # endregion
}
