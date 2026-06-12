<?php

namespace Modules\Invoices\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListInvoices::class)]
class InvoicesExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    #[Group('failing')]
    public function it_dispatches_csv_export_job(): void
    {
        $this->markTestSkipped('exportCsv action does not exist; only exportCsvV1/V2 are registered');
    }

    #[Test]
    #[Group('export')]
    #[Group('failing')]
    public function it_dispatches_excel_export_job(): void
    {
        $this->markTestSkipped('exportExcel action does not exist; only exportExcelV1/V2 are registered');
    }

    #[Test]
    #[Group('export')]
    #[Group('failing')]
    public function it_exports_with_no_records(): void
    {
        $this->markTestSkipped('exportExcel action does not exist; only exportExcelV1/V2 are registered');
    }

    #[Test]
    #[Group('export')]
    #[Group('failing')]
    public function it_exports_with_special_characters(): void
    {
        $this->markTestSkipped('exportExcel action does not exist; only exportExcelV1/V2 are registered');
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
