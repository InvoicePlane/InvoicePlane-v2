<?php

namespace Modules\Quotes\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    public function it_exports_quotes_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $quotes = Quote::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('export')
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
        $this->assertCount($quotes->count() + 1, $lines);
        foreach ($quotes as $quote) {
            $this->assertStringContainsString($quote->number, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_quotes_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $quotes = Quote::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('export', ['format' => 'xlsx'])
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
    public function it_exports_quotes_with_no_records(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        // No quotes created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('export')
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
    public function it_exports_quotes_with_special_characters(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $quotes = Quote::factory()->for($this->company)->create(['number' => 'QÜØTË, "Test"', 'total' => 123.45]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('export')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('QÜØTË', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('123.45', $content);
    }
}
