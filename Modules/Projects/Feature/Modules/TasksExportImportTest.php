<?php

namespace Modules\Projects\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    public function it_exports_tasks_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $tasks = Task::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('exportCsvV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($tasks->count() + 1, $lines);
        foreach ($tasks as $task) {
            $this->assertStringContainsString($task->task_name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $tasks = Task::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('exportExcelV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $content = $response->getContent();
        $this->assertStringStartsWith('PK', $content);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_with_no_records(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        // No tasks created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('exportExcelV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(1, count($lines));
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_with_special_characters(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $tasks = Task::factory()->for($this->company)->create(['task_name' => 'ÜTask, "Test"', 'description' => 'Special chars']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('exportCsvV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertMatchesRegularExpression('/^text\/csv\b/i', $response->headers->get('content-type'));
        $content = $response->getContent();
        $this->assertStringContainsString('ÜTask', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('Special chars', $content);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_csv_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $tasks = Task::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('exportCsvV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($tasks->count() + 1, $lines);
        foreach ($tasks as $task) {
            $this->assertStringContainsString($task->task_name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_csv_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $tasks = Task::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('exportCsvV1')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($tasks->count() + 1, $lines);
        foreach ($tasks as $task) {
            $this->assertStringContainsString($task->task_name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_excel_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $tasks = Task::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('exportExcelV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $content = $response->getContent();
        $this->assertStringStartsWith('PK', $content);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_tasks_downloads_excel_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $tasks = Task::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('exportExcelV1')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $content = $response->getContent();
        $this->assertStringStartsWith('PK', $content);
    }
}
