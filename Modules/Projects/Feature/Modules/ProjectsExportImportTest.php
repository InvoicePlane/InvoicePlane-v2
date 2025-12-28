<?php

namespace Modules\Projects\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Projects\Filament\Company\Resources\Projects\Pages\ListProjects;
use Modules\Projects\Models\Project;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ProjectsExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v2(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $projects = Project::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'project_name' => ['isEnabled' => true, 'label' => 'Project Name'],
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
        $projects = Project::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'project_name' => ['isEnabled' => true, 'label' => 'Project Name'],
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
        // No projects created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'project_name' => ['isEnabled' => true, 'label' => 'Project Name'],
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
        $project = Project::factory()->for($this->company)->create([
            'project_name' => 'ÜProject, "Test"',
            'description' => 'Special chars',
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'project_name' => ['isEnabled' => true, 'label' => 'Project Name'],
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
        $projects = Project::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'project_name' => ['isEnabled' => true, 'label' => 'Project Name'],
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
        $projects = Project::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'project_name' => ['isEnabled' => true, 'label' => 'Project Name'],
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
