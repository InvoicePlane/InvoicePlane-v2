<?php

namespace Modules\Quotes\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
    public function export_quotes_downloads_csv_with_correct_data(): void
    {
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
    public function export_quotes_downloads_excel_with_correct_data(): void
    {
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
    public function export_quotes_with_no_records(): void
    {
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
    public function export_quotes_with_special_characters(): void
    {
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

    #[Test]
    #[Group('import')]
    public function import_quotes_with_empty_file(): void
    {
        /* Arrange */
        $file = UploadedFile::fake()->createWithContent('quotes.csv', '');

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('quotes', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_quotes_with_only_headers(): void
    {
        /* Arrange */
        $file = UploadedFile::fake()->createWithContent('quotes.csv', "number,total\n");

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('quotes', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_quotes_with_invalid_columns(): void
    {
        /* Arrange */
        $file = UploadedFile::fake()->createWithContent('quotes.csv', "foo,bar\nabc,def\n");

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('quotes', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_quotes_with_duplicate_records(): void
    {
        /* Arrange */
        $csv  = "number,total\nDup Quote,100.00\nDup Quote,100.00\n";
        $file = UploadedFile::fake()->createWithContent('quotes.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('quotes', 2);
    }

    #[Test]
    #[Group('import')]
    public function import_quotes_with_invalid_data_types(): void
    {
        /* Arrange */
        $csv  = "number,total\nQ-12345,not-a-number\n";
        $file = UploadedFile::fake()->createWithContent('quotes.csv', $csv);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseHas('quotes', ['number' => 'Q-12345', 'total' => 'not-a-number']);
    }

    #[Test]
    #[Group('import')]
    public function import_quotes_with_large_file(): void
    {
        /* Arrange */
        $rows = [];
        for ($i = 0; $i < 1000; $i++) {
            $rows[] = "Q-{$i},{$i}.00";
        }
        $csv  = "number,total\n" . implode("\n", $rows);
        $file = UploadedFile::fake()->createWithContent('quotes.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('quotes', 1000);
    }

    #[Test]
    #[Group('import')]
    public function import_quotes_with_extra_columns(): void
    {
        /* Arrange */
        $csv  = "number,total,extra\nExtra Quote,123.45,something\n";
        $file = UploadedFile::fake()->createWithContent('quotes.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseHas('quotes', ['number' => 'Extra Quote', 'total' => 123.45]);
    }

    #[Test]
    #[Group('import')]
    public function import_quotes_with_missing_required_columns(): void
    {
        /* Arrange */
        $csv  = "number\nMissing Total\n";
        $file = UploadedFile::fake()->createWithContent('quotes.csv', $csv);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('quotes', 0);
    }
}
