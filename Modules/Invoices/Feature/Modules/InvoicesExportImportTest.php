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
    public function it_exports_invoices_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
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
        $this->assertCount($invoices->count() + 1, $lines);
        foreach ($invoices as $invoice) {
            $this->assertStringContainsString($invoice->number, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_invoices_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $invoices = Invoice::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
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
    public function it_exports_invoices_with_no_records(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        // No invoices created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
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
    public function it_exports_invoices_with_special_characters(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $invoices = Invoice::factory()->for($this->company)->create(['number' => 'INV-Ü, "Test"', 'total' => 123.45]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('exportExcel')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('INV-Ü', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('123.45', $content);
    }
}
