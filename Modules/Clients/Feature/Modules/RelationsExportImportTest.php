<?php

namespace Modules\Clients\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    public function it_exports_relations_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $relations = Relation::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('exportCsv')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(
            in_array(
                $response->headers->get('content-type'),
                [
                    'text/csv',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
        );
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($relations->count() + 1, $lines);
        foreach ($relations as $relation) {
            $this->assertStringContainsString($relation->name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_relations_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $relations = Relation::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('exportExcel')
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
    public function it_exports_relations_with_no_records(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        // No relations created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('exportExcel')
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
    public function it_exports_relations_with_special_characters(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $relations = Relation::factory()->for($this->company)->create(['name' => 'Rëlâtïon, "Test"', 'email' => 'special@example.com']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListRelations::class)
            ->mountAction('exportExcel')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('Rëlâtïon', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('special@example.com', $content);
    }
}
