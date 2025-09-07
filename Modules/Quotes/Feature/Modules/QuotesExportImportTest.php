<?php

namespace Modules\Quotes\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuotesExportImportTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function export_quotes_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $quotes = Quote::factory()->count(3)->create();

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
    #[Group('import')]
    public function import_quotes_creates_records_from_csv(): void
    {
        /* Arrange */
        $csv  = "number,total\nQ-1001,100.00\nQ-1002,200.00\n";
        $file = UploadedFile::fake()->createWithContent('quotes.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('quotes', 2);
        $this->assertDatabaseHas('quotes', ['number' => 'Q-1001', 'total' => 100.00]);
        $this->assertDatabaseHas('quotes', ['number' => 'Q-1002', 'total' => 200.00]);
    }
}
