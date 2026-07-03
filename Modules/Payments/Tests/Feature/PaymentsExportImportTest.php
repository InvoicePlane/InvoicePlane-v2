<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Bus\ChainedBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
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
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
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
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
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
                    'amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
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
        $payment = Payment::factory()->for($this->company)->create([
            'amount' => 123.45,
            'note'   => 'Ü Payment, "Test"',
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
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
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
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
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'amount' => ['isEnabled' => true, 'label' => 'Payment Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertDispatched(ChainedBatch::class);
    }
}
