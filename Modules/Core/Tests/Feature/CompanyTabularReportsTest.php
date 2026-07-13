<?php

namespace Modules\Core\Tests\Feature;

use Modules\Clients\Models\Relation;
use Modules\Core\Filament\Company\Pages\Reports\InvoicedByClientReport;
use Modules\Core\Filament\Company\Pages\Reports\InvoicesPerClientReport;
use Modules\Core\Filament\Company\Pages\Reports\InvoicingHistoryReport;
use Modules\Core\Filament\Company\Pages\Reports\PaymentHistoryReport;
use Modules\Core\Filament\Company\Pages\Reports\SalesByDateReport;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\Test;

class CompanyTabularReportsTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_renders_every_report_page(): void
    {
        $relation = Relation::factory()->create(['company_id' => $this->company->id]);
        Invoice::factory()->create([
            'company_id'  => $this->company->id,
            'customer_id' => $relation->id,
            'user_id'     => $this->user->id,
            'invoiced_at' => now()->toDateString(),
        ]);

        foreach ([
            PaymentHistoryReport::class,
            InvoicingHistoryReport::class,
            InvoicedByClientReport::class,
            SalesByDateReport::class,
            InvoicesPerClientReport::class,
        ] as $page) {
            $this->testLivewire($page)->assertSuccessful();
        }
    }

    #[Test]
    public function it_lists_payments_in_the_selected_date_range_only(): void
    {
        /* Arrange */
        $payment = $this->makePayment(['paid_at' => now()->subDays(3)]);
        $old     = $this->makePayment(['paid_at' => now()->subMonths(2)]);

        /* Act & Assert */
        $this->testLivewire(PaymentHistoryReport::class)
            ->set('dateFrom', now()->subMonth()->toDateString())
            ->set('dateTo', now()->toDateString())
            ->assertCanSeeTableRecords([$payment])
            ->assertCanNotSeeTableRecords([$old]);
    }

    #[Test]
    public function it_sums_the_invoiced_amount_per_client(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create();
        Invoice::factory()->for($this->company)->for($relation, 'customer')->create([
            'user_id'       => $this->user->id,
            'invoiced_at'   => now()->toDateString(),
            'invoice_total' => 1000,
        ]);
        Invoice::factory()->for($this->company)->for($relation, 'customer')->create([
            'user_id'       => $this->user->id,
            'invoiced_at'   => now()->toDateString(),
            'invoice_total' => 500,
        ]);

        /* Act & Assert */
        $this->testLivewire(InvoicedByClientReport::class)
            ->assertSee('1500');
    }

    #[Test]
    public function it_scopes_reports_to_the_current_company(): void
    {
        /* Arrange */
        $otherCompany = Company::factory()->create();
        $otherPayment = $this->makePayment(['paid_at' => now()->subDay()], $otherCompany);

        /* Act & Assert */
        $this->testLivewire(PaymentHistoryReport::class)
            ->assertCanNotSeeTableRecords([$otherPayment]);
    }

    #[Test]
    public function it_filters_payments_by_client(): void
    {
        /* Arrange */
        $clientA  = Relation::factory()->for($this->company)->create();
        $clientB  = Relation::factory()->for($this->company)->create();
        $paymentA = $this->makePayment(['customer_id' => $clientA->id, 'paid_at' => now()->subDay()]);
        $paymentB = $this->makePayment(['customer_id' => $clientB->id, 'paid_at' => now()->subDay()]);

        /* Act & Assert */
        $this->testLivewire(PaymentHistoryReport::class)
            ->set('dateFrom', now()->subWeek()->toDateString())
            ->set('dateTo', now()->toDateString())
            ->set('clientId', $clientA->id)
            ->assertCanSeeTableRecords([$paymentA])
            ->assertCanNotSeeTableRecords([$paymentB]);
    }

    #[Test]
    public function it_counts_only_paid_invoices_in_the_sales_by_date_report(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create();
        Invoice::factory()->for($this->company)->for($relation, 'customer')->create([
            'user_id'        => $this->user->id,
            'invoiced_at'    => now()->toDateString(),
            'invoice_status' => 'paid',
            'invoice_total'  => 777,
        ]);
        Invoice::factory()->for($this->company)->for($relation, 'customer')->create([
            'user_id'        => $this->user->id,
            'invoiced_at'    => now()->toDateString(),
            'invoice_status' => 'draft',
            'invoice_total'  => 999999,
        ]);

        /* Act & Assert */
        $this->testLivewire(SalesByDateReport::class)
            ->assertSee('777')
            ->assertDontSee('999999');
    }

    #[Test]
    public function it_exports_the_filtered_rows_as_csv(): void
    {
        /* Arrange */
        $this->makePayment(['paid_at' => now()->subDay()]);

        /* Act & Assert */
        $this->testLivewire(PaymentHistoryReport::class)
            ->set('dateFrom', now()->subWeek()->toDateString())
            ->set('dateTo', now()->toDateString())
            ->callTableAction('exportCsv')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }

    protected function makePayment(array $attributes = [], ?Company $company = null): Payment
    {
        $company ??= $this->company;

        $relation = Relation::factory()->create(['company_id' => $company->id]);
        $invoice  = Invoice::factory()->create([
            'company_id'  => $company->id,
            'customer_id' => $attributes['customer_id'] ?? $relation->id,
            'user_id'     => $this->user->id,
        ]);

        return Payment::factory()->for($company)->create(array_merge([
            'customer_id' => $invoice->customer_id,
            'invoice_id'  => $invoice->id,
        ], $attributes));
    }
}
