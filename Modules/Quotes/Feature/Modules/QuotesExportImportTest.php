<?php

namespace Modules\Quotes\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuotesExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v2(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $quotes = Quote::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'number' => ['isEnabled' => true, 'label' => 'Quote Number'],
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
        $this->markTestIncomplete();

        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $quotes = Quote::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
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
    public function it_exports_with_no_records(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        // No quotes created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
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
        $this->markTestIncomplete();

        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $quote = Quote::factory()->for($this->company)->create([
            'number' => 'QÜØTË, "Test"',
            'total'  => 123.45,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
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
}
