<?php

namespace Modules\Invoices\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoicesExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function export_invoices_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
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
        $this->assertCount($invoices->count() + 1, $lines);
        foreach ($invoices as $invoice) {
            $this->assertStringContainsString($invoice->number, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function export_invoices_downloads_excel_with_correct_data(): void
    {
        /* Arrange */
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
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
    public function export_invoices_with_no_records(): void
    {
        /* Arrange */
        // No invoices created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
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
    public function export_invoices_with_special_characters(): void
    {
        /* Arrange */
        $invoices = Invoice::factory()->for($this->company)->create(['number' => 'INV-Ü, "Test"', 'total' => 123.45]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('export')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('INV-Ü', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('123.45', $content);
    }

    #[Test]
    #[Group('import')]
    public function import_invoices_with_empty_file(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('invoices.csv', '');

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('invoices', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_invoices_with_only_headers(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('invoices.csv', "number,total\n");

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('invoices', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_invoices_with_invalid_columns(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('invoices.csv', "foo,bar\nabc,def\n");

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('invoices', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_invoices_creates_records_from_csv(): void
    {
        /* Arrange */
        $csv  = "number,total\nINV-1001,100.00\nINV-1002,200.00\n";
        $file = UploadedFile::fake()->createWithContent('invoices.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('invoices', 2);
        $this->assertDatabaseHas('invoices', ['number' => 'INV-1001', 'total' => 100.00]);
        $this->assertDatabaseHas('invoices', ['number' => 'INV-1002', 'total' => 200.00]);
    }
}
