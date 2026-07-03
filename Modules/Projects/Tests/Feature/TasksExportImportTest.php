<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Bus\ChainedBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Projects\Filament\Company\Resources\Tasks\Pages\ListTasks;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class TasksExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v2(): void
    {
        /* Arrange */
        Bus::fake();
        Storage::fake('local');
        $tasks = Task::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'task_name' => ['isEnabled' => true, 'label' => 'Task Name'],
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
        $tasks = Task::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'task_name' => ['isEnabled' => true, 'label' => 'Task Name'],
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
        // No tasks created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'task_name' => ['isEnabled' => true, 'label' => 'Task Name'],
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
        $task = Task::factory()->for($this->company)->create([
            'task_name'   => 'ÜTask, "Test"',
            'description' => 'Special chars',
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'task_name' => ['isEnabled' => true, 'label' => 'Task Name'],
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
        $tasks = Task::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'task_name' => ['isEnabled' => true, 'label' => 'Task Name'],
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
        $tasks = Task::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'task_name' => ['isEnabled' => true, 'label' => 'Task Name'],
                ],
            ]);

        /* Assert */
        Bus::assertDispatched(ChainedBatch::class);
    }
}
