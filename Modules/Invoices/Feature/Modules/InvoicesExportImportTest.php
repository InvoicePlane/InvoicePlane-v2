<?php

namespace Modules\Invoices\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoicesExportImportTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function export_invoices_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $invoices = Invoice::factory()->count(3)->create();

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
