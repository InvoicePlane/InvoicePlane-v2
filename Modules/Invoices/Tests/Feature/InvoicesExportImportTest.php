<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoicesExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('exportCsv', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Invoice Number'],
                    'total'  => ['isEnabled' => true, 'label' => 'Total'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_excel_export_job(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('exportExcel', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Invoice Number'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_with_no_records(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        // No invoices created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('exportExcel', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Number'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_with_special_characters(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $invoice = Invoice::factory()->for($this->company)->create([
            'number' => 'INV-Ü, "Test"',
            'total'  => 123.45,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('exportExcel', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Number'],
                    'total'  => ['isEnabled' => true, 'label' => 'Total'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v2(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Number'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v1(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Number'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_excel_export_job_v2(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Number'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_excel_export_job_v1(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Number'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }
}
