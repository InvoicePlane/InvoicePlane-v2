<?php

namespace Modules\Projects\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    public function it_exports_projects_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $projects = Project::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('exportCsvV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($projects->count() + 1, $lines);
        foreach ($projects as $project) {
            $this->assertStringContainsString($project->project_name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $projects = Project::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
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
    public function it_exports_projects_with_no_records(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        // No projects created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('exportExcelV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', trim($content));
        $this->assertGreaterThanOrEqual(1, count($lines));
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_with_special_characters(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $projects = Project::factory()->for($this->company)->create(['project_name' => 'ÜProject, "Test"', 'description' => 'Special chars']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('exportExcelV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('ÜProject', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('Special chars', $content);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_csv_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $projects = Project::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('exportCsvV2')
            ->callMountedAction();
        $response = $component->lastResponse;
        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($projects->count() + 1, $lines);
        foreach ($projects as $project) {
            $this->assertStringContainsString($project->project_name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_csv_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $projects = Project::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('exportCsvV1')
            ->callMountedAction();
        $response = $component->lastResponse;
        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($projects->count() + 1, $lines);
        foreach ($projects as $project) {
            $this->assertStringContainsString($project->project_name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_projects_downloads_excel_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $projects = Project::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
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
    public function it_exports_projects_downloads_excel_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $projects = Project::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProjects::class)
            ->mountAction('exportExcelV1')
            ->callMountedAction();
        $response = $component->lastResponse;
        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }
}
