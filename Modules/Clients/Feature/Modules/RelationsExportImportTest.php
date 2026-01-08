<?php

namespace Modules\Clients\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ListRelations;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class RelationsExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v2(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $relations = Relation::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Relation Name'],
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
        $relations = Relation::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Relation Name'],
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
        // No relations created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Relation Name'],
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
        $relation = Relation::factory()->for($this->company)->create([
            'name' => 'ÜRelation, "Test"',
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Relation Name'],
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
        $relations = Relation::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Relation Name'],
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
        $relations = Relation::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Relation Name'],
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
