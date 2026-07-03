<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\UserRole;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\ListExpenses;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Payments\Filament\Company\Resources\Payments\Pages\ListPayments;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/*
 * The company panel test base freezes Carbon at 2026-01-01, so "today"
 * is always 2026-01-01 and today+30 is 2026-01-31 in these assertions.
 */
class DateFieldDefaultsTest extends AbstractCompanyPanelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Resource pages gate on Spatie permissions, so the test user
         * needs the seeded client_admin permission set to mount pages.
         */
        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
        $this->user->assignRole(UserRole::CUSTOMER_ADMIN->value);
    }

    #[Test]
    #[Group('crud')]
    public function it_pre_fills_invoice_dates_with_today_and_due_in_thirty_days(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->assertActionDataSet([
                'invoiced_at'    => '2026-01-01',
                'invoice_due_at' => '2026-01-31',
            ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_pre_fills_quote_dates_with_today_and_expiry_in_thirty_days(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('create')
            ->assertActionDataSet([
                'quoted_at'        => '2026-01-01',
                'quote_expires_at' => '2026-01-31',
            ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_pre_fills_expense_date_with_today(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('create')
            ->assertActionDataSet([
                'expensed_at' => '2026-01-01',
            ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_pre_fills_payment_date_with_today(): void
    {
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('create')
            ->assertActionDataSet([
                'paid_at' => '2026-01-01',
            ]);
    }
}
