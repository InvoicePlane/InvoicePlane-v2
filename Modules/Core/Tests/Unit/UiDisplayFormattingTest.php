<?php

namespace Modules\Core\Tests\Unit;

use Carbon\Carbon as BaseCarbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentTimezone;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Modules\Core\Models\Company;
use Modules\Core\Models\Setting;
use Modules\Core\Support\DateHelpers;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Tables\ExpenseItemsTable;
use Modules\Expenses\Filament\Company\Resources\Expenses\Tables\ExpensesTable;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Tables\InvoiceItemsTable;
use Modules\Invoices\Filament\Company\Resources\Invoices\Tables\InvoicesTable;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Tables\RecurringInvoiceItemsTable;
use Modules\Payments\Filament\Company\Resources\Payments\Tables\PaymentsTable;
use Modules\Projects\Filament\Company\Resources\Tasks\Tables\TasksTable;
use Modules\Quotes\Filament\Company\Resources\Quotes\Tables\QuotesTable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class UiDisplayFormattingTest extends AbstractTestCase
{
    use RefreshDatabase;

    public static function amountColumns(): array
    {
        return [
            'invoice total'         => [InvoicesTable::class, 'invoice_total'],
            'quote total'           => [QuotesTable::class, 'quote_total'],
            'payment amount'        => [PaymentsTable::class, 'payment_amount'],
            'expense amount'        => [ExpensesTable::class, 'expense_amount'],
            'invoice item subtotal' => [InvoiceItemsTable::class, 'subtotal'],
            'expense item discount' => [ExpenseItemsTable::class, 'discount'],
            'recurring item tax'    => [RecurringInvoiceItemsTable::class, 'tax_total'],
        ];
    }

    public static function quantityColumns(): array
    {
        return [[InvoiceItemsTable::class], [ExpenseItemsTable::class], [RecurringInvoiceItemsTable::class]];
    }

    #[Test]
    public function date_fallbacks_follow_the_locale_and_explicit_settings_take_precedence(): void
    {
        app()->setLocale('de');
        $this->assertSame('14.07.2025', DateHelpers::formatDate('2025-07-14'));
        app()->setLocale('en');
        $this->assertSame('2025-07-14', DateHelpers::formatDate('2025-07-14'));

        Setting::saveByKey(Setting::KEY_DATE_FORMAT, 'd/m/Y');
        app()->setLocale('de');
        $this->assertSame('14/07/2025', DateHelpers::formatDate('2025-07-14'));
    }

    #[Test]
    public function tenant_date_settings_override_global_settings_and_resolve_lazily(): void
    {
        $company = Company::factory()->create();
        Setting::saveByKey(Setting::KEY_DATE_FORMAT, 'm/d/Y');
        Setting::saveForCompany($company->id, Setting::KEY_DATE_FORMAT, 'd.m.Y');
        $table = $this->table();

        Filament::setTenant($company, isQuiet: true);
        $this->assertSame('d.m.Y', $table->getDefaultDateDisplayFormat());
        $this->assertSame('14.07.2025', DateHelpers::formatDate('2025-07-14'));
        Filament::setTenant(null);
        $this->assertSame('m/d/Y', $table->getDefaultDateDisplayFormat());
    }

    #[Test]
    public function relative_dates_use_the_app_locale_without_mutating_the_carbon_input(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-25 12:00:00', 'UTC'));
        $date   = BaseCarbon::parse('2026-10-22 12:00:00', 'UTC')->locale('en');
        $before = $date->toIso8601String();
        app()->setLocale('de');

        $this->assertStringContainsString('vor', DateHelpers::formatSince($date));
        $this->assertSame('en', $date->locale());
        $this->assertSame($before, $date->toIso8601String());
        app()->setLocale('en');
        $this->assertStringContainsString('ago', DateHelpers::formatSince($date));
        Carbon::setTestNow();
    }

    #[Test]
    public function table_schema_and_picker_display_defaults_preserve_storage_formats(): void
    {
        app()->setLocale('en');
        $table  = $this->table();
        $schema = Schema::make();
        $picker = DatePicker::make('issued_at')->native(false);
        app()->setLocale('de');

        $this->assertSame('d.m.Y', $table->getDefaultDateDisplayFormat());
        $this->assertSame('d.m.Y H:i:s', $table->getDefaultDateTimeDisplayFormat());
        $this->assertSame('d.m.Y', $schema->getDefaultDateDisplayFormat());
        $this->assertSame('d.m.Y', $picker->getDisplayFormat());
        $this->assertSame('Y-m-d', $picker->getFormat());
        $this->assertSame('Y-m-d', DatePicker::make('native_date')->getFormat());

        app()->setLocale('en');
        $this->assertSame('Y-m-d', $table->getDefaultDateDisplayFormat());
        $this->assertSame('Y-m-d', $picker->getDisplayFormat());
    }

    #[Test]
    public function date_only_values_do_not_move_to_the_previous_day_for_the_display_timezone(): void
    {
        app()->setLocale('de');
        config(['app.timezone' => 'UTC']);
        FilamentTimezone::set('America/Los_Angeles');
        $table = $this->table()->columns([TextColumn::make('invoice_due_at')->date()]);
        $this->assertSame('25.10.2026', $table->getColumn('invoice_due_at')->formatState('2026-10-25'));
        $date = Carbon::parse('2026-10-25 00:00:00', 'Pacific/Kiritimati');
        $this->assertSame('25.10.2026', DateHelpers::formatDate($date));
        $this->assertSame('Pacific/Kiritimati', $date->getTimezone()->getName());
    }

    #[Test]
    #[DataProvider('amountColumns')]
    public function actual_amount_columns_render_two_localized_decimals(string $tableClass, string $columnName): void
    {
        $column = $tableClass::configure($this->table())->getColumn($columnName);
        app()->setLocale('de');
        $this->assertSame('1.234,50', $column->formatState('1234.5'));
        app()->setLocale('en');
        $this->assertSame('1,234.50', $column->formatState('1234.5'));
    }

    #[Test]
    #[DataProvider('quantityColumns')]
    public function quantities_preserve_up_to_eight_decimal_places(string $tableClass): void
    {
        $column = $tableClass::configure($this->table())->getColumn('quantity');
        app()->setLocale('de');
        $this->assertSame('1,12345678', $column->formatState('1.12345678'));
        $this->assertSame('2', $column->formatState('2'));
        app()->setLocale('en');
        $this->assertSame('1.12345678', $column->formatState('1.12345678'));
    }

    #[Test]
    public function money_columns_use_the_current_company_currency_and_global_fallback(): void
    {
        $swiss   = Company::factory()->create();
        $british = Company::factory()->create();
        Setting::saveForCompany($swiss->id, Setting::KEY_CURRENCY_CODE, 'CHF');
        Setting::saveForCompany($british->id, Setting::KEY_CURRENCY_CODE, 'GBP');
        Setting::saveByKey(Setting::KEY_CURRENCY_CODE, 'CAD');
        app()->setLocale('de');
        $table  = TasksTable::configure($this->table());
        $column = $table->getColumn('task_price');

        Filament::setTenant($swiss, isQuiet: true);
        $this->assertSame('CHF', $table->getDefaultCurrency());
        $this->assertSame(Number::currency(1234.5, 'CHF', 'de'), $column->formatState('1234.5'));
        Filament::setTenant($british, isQuiet: true);
        $this->assertSame(Number::currency(1234.5, 'GBP', 'de'), $column->formatState('1234.5'));
        Filament::setTenant(null);
        $this->assertSame('CAD', $table->getDefaultCurrency());
    }

    private function table(): Table
    {
        return Table::make(new ListInvoices());
    }
}
