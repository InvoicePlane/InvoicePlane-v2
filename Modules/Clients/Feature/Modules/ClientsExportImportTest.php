<?php

namespace Modules\Clients\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\Clients\Pages\ListClients;
use Modules\Clients\Models\Client;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ClientsExportImportTest extends AbstractCompanyPanelTestCase
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
        $clients = Client::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListClients::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'company_name' => ['isEnabled' => true, 'label' => 'Company Name'],
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
        $this->markTestIncomplete();
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $clients = Client::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListClients::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'company_name' => ['isEnabled' => true, 'label' => 'Company Name'],
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
        // No clients created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListClients::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'company_name' => ['isEnabled' => true, 'label' => 'Company Name'],
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
        $client = Client::factory()->for($this->company)->create([
            'company_name' => 'ÜClient, "Test"',
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListClients::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'company_name' => ['isEnabled' => true, 'label' => 'Company Name'],
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
        $clients = Client::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListClients::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'company_name' => ['isEnabled' => true, 'label' => 'Company Name'],
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
        $clients = Client::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListClients::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'company_name' => ['isEnabled' => true, 'label' => 'Company Name'],
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
