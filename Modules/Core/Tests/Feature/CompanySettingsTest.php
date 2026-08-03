<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Company\Pages\CompanySettings;
use Modules\Core\Models\Company;
use Modules\Core\Models\Setting;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CompanySettings::class)]
class CompanySettingsTest extends AbstractCompanyPanelTestCase
{
    # region page renders
    #[Test]
    #[Group('smoke')]
    public function it_renders_the_settings_page(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CompanySettings::class);

        /* Assert */
        $component->assertSuccessful();
    }
    # endregion

    # region per-company save/load
    #[Test]
    #[Group('per-company')]
    public function it_persists_a_saved_setting_for_the_current_company_only(): void
    {
        /* Arrange */
        $other = Company::factory()->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->set('data.' . Setting::KEY_COMPANY_NAME, 'Acme Corp')
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertSame('Acme Corp', Setting::getForCompany($this->company->id, Setting::KEY_COMPANY_NAME));
        $this->assertNull(Setting::getForCompany($other->id, Setting::KEY_COMPANY_NAME, null, true));
    }

    #[Test]
    #[Group('per-company')]
    public function it_persists_boolean_toggles_as_one_or_zero(): void
    {
        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->set('data.' . Setting::KEY_DASHBOARD_SHOW_REVENUE_CHART, false)
            ->set('data.' . Setting::KEY_INVOICE_QR_CODE_ENABLED, true)
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertSame('0', Setting::getForCompany($this->company->id, Setting::KEY_DASHBOARD_SHOW_REVENUE_CHART));
        $this->assertSame('1', Setting::getForCompany($this->company->id, Setting::KEY_INVOICE_QR_CODE_ENABLED));
        $this->assertTrue(Setting::getBoolForCompany($this->company->id, Setting::KEY_INVOICE_QR_CODE_ENABLED));
    }

    #[Test]
    #[Group('per-company')]
    public function it_persists_a_long_text_setting(): void
    {
        /* Arrange */
        $text = "Payment due within 30 days.\nThank you for your business.";

        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->set('data.' . Setting::KEY_INVOICE_DEFAULT_TERMS, $text)
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertSame($text, Setting::getForCompany($this->company->id, Setting::KEY_INVOICE_DEFAULT_TERMS));
    }

    #[Test]
    #[Group('per-company')]
    public function it_persists_company_branding_settings(): void
    {
        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanySettings::class)
            ->set('data.' . Setting::KEY_PRIMARY_COLOR, '#ff0000')
            ->set('data.' . Setting::KEY_ACCENT_COLOR, '#00ff00')
            ->set('data.' . Setting::KEY_FONT_FAMILY, 'Roboto')
            ->set('data.' . Setting::KEY_FONT_SIZE, 16)
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertSame('#ff0000', Setting::getForCompany($this->company->id, Setting::KEY_PRIMARY_COLOR));
        $this->assertSame('#00ff00', Setting::getForCompany($this->company->id, Setting::KEY_ACCENT_COLOR));
        $this->assertSame('Roboto', Setting::getForCompany($this->company->id, Setting::KEY_FONT_FAMILY));
        $this->assertSame('16', Setting::getForCompany($this->company->id, Setting::KEY_FONT_SIZE));
    }

    #[Test]
    #[Group('per-company')]
    public function it_prefills_form_state_from_existing_settings(): void
    {
        /* Arrange */
        Setting::saveForCompany($this->company->id, Setting::KEY_COMPANY_NAME, 'Pre-filled Co');
        Setting::saveForCompany($this->company->id, Setting::KEY_CURRENCY_CODE, 'EUR');

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CompanySettings::class);

        $data = $component->get('data');

        /* Assert */
        $this->assertSame('Pre-filled Co', $data[Setting::KEY_COMPANY_NAME] ?? null);
        $this->assertSame('EUR', $data[Setting::KEY_CURRENCY_CODE] ?? null);
    }
    # endregion

    # region getForCompany / getBoolForCompany
    #[Test]
    #[Group('per-company')]
    public function get_for_company_falls_back_to_global_when_no_company_row(): void
    {
        /* Arrange */
        Setting::saveByKey('legacy_key', 'global-value');

        /* Assert */
        $this->assertSame('global-value', Setting::getForCompany($this->company->id, 'legacy_key'));
    }

    #[Test]
    #[Group('per-company')]
    public function get_for_company_returns_default_when_nothing_set(): void
    {
        $this->assertSame('fallback', Setting::getForCompany($this->company->id, 'unrelated_key', 'fallback'));
    }

    #[Test]
    #[Group('per-company')]
    public function get_for_company_company_only_skips_global_fallback(): void
    {
        Setting::saveByKey('legacy_key', 'global-value');

        $this->assertNull(Setting::getForCompany($this->company->id, 'legacy_key', null, true));
    }

    #[Test]
    #[Group('per-company')]
    public function company_scoped_value_wins_over_global(): void
    {
        Setting::saveByKey('shared_key', 'global');
        Setting::saveForCompany($this->company->id, 'shared_key', 'company');

        $this->assertSame('company', Setting::getForCompany($this->company->id, 'shared_key'));
    }

    #[Test]
    #[Group('per-company')]
    public function save_for_company_is_idempotent_for_same_company_and_key(): void
    {
        /* Arrange */
        Setting::saveForCompany($this->company->id, 'k1', 'first');

        /* Act: second save for same company+key should update, not duplicate */
        Setting::saveForCompany($this->company->id, 'k1', 'second');

        /* Assert: only one row, value updated */
        $rows = Setting::query()->withoutGlobalScopes()
            ->where('company_id', $this->company->id)
            ->where('setting_key', 'k1')
            ->get();

        $this->assertCount(1, $rows);
        $this->assertSame('second', $rows->first()->setting_value);
    }

    #[Test]
    #[Group('per-company')]
    public function partial_unique_index_allows_same_key_across_companies(): void
    {
        /* Arrange */
        $other = Company::factory()->create();

        Setting::saveForCompany($this->company->id, 'currency_code', 'USD');
        Setting::saveForCompany($other->id, 'currency_code', 'EUR');

        $this->assertSame('USD', Setting::getForCompany($this->company->id, 'currency_code'));
        $this->assertSame('EUR', Setting::getForCompany($other->id, 'currency_code'));
    }
    # endregion

    # region access control
    #[Test]
    #[Group('access')]
    public function a_user_without_manage_company_settings_cannot_access(): void
    {
        /* Arrange: a user with no permissions assigned */
        $unprivileged = \Modules\Core\Models\User::factory()->create();

        /* Act & Assert: canAccess() returns false */
        // authenticate then check
        \Filament\Facades\Filament::auth()->login($unprivileged);
        $this->assertFalse(CompanySettings::canAccess());
    }
    # endregion
}
