<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Bus\ChainedBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Filament\Company\Resources\Payments\Pages\ListPayments;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class PaymentsExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v2(): void
    {
        /* Arrange */
        Bus::fake();
        Storage::fake('local');
        $payments = $this->createPayments(3);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'payment_amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertDispatched(ChainedBatch::class);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_excel_export_job_v2(): void
    {
        /* Arrange */
        Bus::fake();
        Storage::fake('local');
        $payments = $this->createPayments(3);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'payment_amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertDispatched(ChainedBatch::class);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_with_no_records(): void
    {
        /* Arrange */
        Bus::fake();
        Storage::fake('local');
        // No payments created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'payment_amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertDispatched(ChainedBatch::class);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_with_special_characters(): void
    {
        /* Arrange */
        Bus::fake();
        Storage::fake('local');
        $payment = $this->createPayments(1, [
            'payment_amount' => 123.45,
            'notes'          => 'Ü Payment, "Test"',
        ])->first();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'payment_amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertDispatched(ChainedBatch::class);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v1(): void
    {
        /* Arrange */
        Bus::fake();
        Storage::fake('local');
        $payments = $this->createPayments(3);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'payment_amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertDispatched(ChainedBatch::class);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_excel_export_job_v1(): void
    {
        /* Arrange */
        Bus::fake();
        Storage::fake('local');
        $payments = $this->createPayments(3);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'payment_amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertDispatched(ChainedBatch::class);
    }

    /**
     * payments.invoice_id is NOT NULL, so every payment needs an invoice.
     */
    private function createPayments(int $count, array $attributes = [])
    {
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id'  => $customer->id,
            'numbering_id' => Numbering::factory()->for($this->company)->create()->id,
            'user_id'      => $this->user->id,
        ]);

        return Payment::factory()->for($this->company)->count($count)->create(array_merge([
            'customer_id' => $customer->id,
            'invoice_id'  => $invoice->id,
        ], $attributes));
    }
}
