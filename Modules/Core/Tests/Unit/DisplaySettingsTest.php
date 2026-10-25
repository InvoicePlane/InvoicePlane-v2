<?php

namespace Modules\Core\Tests\Unit;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Company;
use Modules\Core\Models\Setting;
use Modules\Core\Support\DateHelpers;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use PHPUnit\Framework\Attributes\Test;

class DisplaySettingsTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        DB::disableQueryLog();
        Filament::setTenant(null);
        parent::tearDown();
    }

    #[Test]
    public function repeated_cells_share_company_and_global_setting_queries(): void
    {
        $company = Company::factory()->create();
        Setting::saveByKey(Setting::KEY_DATE_FORMAT, 'd/m/Y');
        Setting::saveByKey(Setting::KEY_CURRENCY_CODE, 'CHF');
        Filament::setTenant($company, isQuiet: true);
        app()->setLocale('de');
        $table = $this->table()->columns([
            TextColumn::make('issued_at')->date(),
            TextColumn::make('total')->money(),
        ]);
        $schema = Schema::make();
        $picker = DatePicker::make('issued_at')->native(false);
        DB::enableQueryLog();
        DB::flushQueryLog();

        for ($row = 0; $row < 20; $row++) {
            $this->assertSame('25/10/2026', $table->getColumn('issued_at')->formatState('2026-10-25'));
            $this->assertStringContainsString('CHF', $table->getColumn('total')->formatState('1234.5'));
            $this->assertSame('25/10/2026', DateHelpers::formatDate('2026-10-25'));
            $this->assertSame('d/m/Y H:i:s', $table->getDefaultDateTimeDisplayFormat());
            $this->assertSame('d/m/Y', $schema->getDefaultDateDisplayFormat());
            $this->assertSame('d/m/Y', $picker->getDisplayFormat());
        }

        $this->assertGreaterThan(0, count($this->settingQueries()));
        $this->assertLessThanOrEqual(4, count($this->settingQueries()));
    }

    #[Test]
    public function tenant_switches_reuse_the_right_company_values(): void
    {
        $swiss   = Company::factory()->create();
        $british = Company::factory()->create();
        Setting::saveForCompany($swiss->id, Setting::KEY_DATE_FORMAT, 'd.m.Y');
        Setting::saveForCompany($swiss->id, Setting::KEY_CURRENCY_CODE, 'CHF');
        Setting::saveForCompany($british->id, Setting::KEY_DATE_FORMAT, 'd/m/Y');
        Setting::saveForCompany($british->id, Setting::KEY_CURRENCY_CODE, 'GBP');
        $table = $this->table();
        DB::enableQueryLog();
        DB::flushQueryLog();

        for ($switch = 0; $switch < 3; $switch++) {
            Filament::setTenant($swiss, isQuiet: true);
            $this->assertSame('d.m.Y', $table->getDefaultDateDisplayFormat());
            $this->assertSame('CHF', $table->getDefaultCurrency());
            Filament::setTenant($british, isQuiet: true);
            $this->assertSame('d/m/Y', $table->getDefaultDateDisplayFormat());
            $this->assertSame('GBP', $table->getDefaultCurrency());
        }

        $this->assertLessThanOrEqual(4, count($this->settingQueries()));
    }

    #[Test]
    public function saved_deleted_and_eventless_global_settings_refresh_existing_components(): void
    {
        $company = Company::factory()->create();
        Setting::saveByKey(Setting::KEY_DATE_FORMAT, 'm/d/Y');
        Setting::saveByKey(Setting::KEY_CURRENCY_CODE, 'CAD');
        Filament::setTenant($company, isQuiet: true);
        $table = $this->table();
        $this->assertSame('m/d/Y', $table->getDefaultDateDisplayFormat());
        $this->assertSame('CAD', $table->getDefaultCurrency());

        Setting::saveForCompany($company->id, Setting::KEY_DATE_FORMAT, 'd.m.Y');
        Setting::saveForCompany($company->id, Setting::KEY_CURRENCY_CODE, 'CHF');
        $this->assertSame('d.m.Y', $table->getDefaultDateDisplayFormat());
        $this->assertSame('CHF', $table->getDefaultCurrency());

        $currency = Setting::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('setting_key', Setting::KEY_CURRENCY_CODE)
            ->firstOrFail();
        $currency->setting_value = 'GBP';
        $currency->save();
        $this->assertSame('GBP', $table->getDefaultCurrency());
        $currency->delete();
        $this->assertSame('CAD', $table->getDefaultCurrency());

        Setting::saveByKey(Setting::KEY_CURRENCY_CODE, 'EUR');
        $this->assertSame('EUR', $table->getDefaultCurrency());
        Setting::saveForCompany($company->id, Setting::KEY_DATE_FORMAT, '');
        $this->assertSame('m/d/Y', $table->getDefaultDateDisplayFormat());
        Setting::saveByKey(Setting::KEY_DATE_FORMAT, 'Y/m/d');
        $this->assertSame('Y/m/d', $table->getDefaultDateDisplayFormat());

        Filament::setTenant(null);
        $globalDate = Setting::withoutGlobalScopes()
            ->whereNull('company_id')
            ->where('setting_key', Setting::KEY_DATE_FORMAT)
            ->firstOrFail();
        $globalDate->delete();
        app()->setLocale('de');
        $this->assertSame('d.m.Y', $table->getDefaultDateDisplayFormat());
    }

    #[Test]
    public function cached_missing_settings_keep_locale_and_configuration_fallbacks_lazy(): void
    {
        Filament::setTenant(null);
        $table = $this->table();
        DB::enableQueryLog();
        DB::flushQueryLog();
        app()->setLocale('de');
        config(['ip.currency_code' => 'EUR']);
        $this->assertSame('d.m.Y', $table->getDefaultDateDisplayFormat());
        $this->assertSame('EUR', $table->getDefaultCurrency());
        app()->setLocale('en');
        config(['ip.currency_code' => 'GBP']);
        $this->assertSame('Y-m-d', $table->getDefaultDateDisplayFormat());
        $this->assertSame('GBP', $table->getDefaultCurrency());
        $this->assertLessThanOrEqual(2, count($this->settingQueries()));
    }

    #[Test]
    public function a_new_lifecycle_reads_settings_again_instead_of_reusing_previous_values(): void
    {
        Filament::setTenant(null);
        Setting::saveByKey(Setting::KEY_DATE_FORMAT, 'd/m/Y');
        $table = $this->table();
        $this->assertSame('d/m/Y', $table->getDefaultDateDisplayFormat());
        // A change made outside this request does not dispatch local model events.
        DB::table('settings')->whereNull('company_id')
            ->where('setting_key', Setting::KEY_DATE_FORMAT)
            ->update(['setting_value' => 'd.m.Y']);
        $this->assertSame('d/m/Y', $table->getDefaultDateDisplayFormat());

        app()->forgetScopedInstances();
        $this->assertSame('d.m.Y', $table->getDefaultDateDisplayFormat());
    }

    private function table(): Table
    {
        return Table::make(new ListInvoices());
    }

    private function settingQueries(): array
    {
        return array_filter(DB::getQueryLog(), fn (array $query): bool => str_contains($query['query'], 'settings'));
    }
}
