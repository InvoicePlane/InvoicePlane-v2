<?php

namespace Modules\Payments\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Payments\Filament\Company\Resources\Payments\Pages\ListPayments;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class PaymentsExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_exports_payments_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('exportCsv')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertContains(
            $response->headers->get('content-type'),
            [
                'text/csv',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($payments->count() + 1, $lines);
        foreach ($payments as $payment) {
            $this->assertStringContainsString((string) $payment->amount, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_payments_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
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
    public function it_exports_payments_with_no_records(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        // No payments created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
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
    public function it_exports_payments_with_special_characters(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->create(['amount' => 123.45, 'reference' => 'REF-Ü, "Test"']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('exportExcel')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('REF-Ü', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('123.45', $content);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_payments_downloads_csv_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('exportCsvV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($payments->count() + 1, $lines);
        foreach ($payments as $payment) {
            $this->assertStringContainsString((string) $payment->amount, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_payments_downloads_csv_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('exportCsvV1')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($payments->count() + 1, $lines);
        foreach ($payments as $payment) {
            $this->assertStringContainsString((string) $payment->amount, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_payments_downloads_excel_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
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
    public function it_exports_payments_downloads_excel_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
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
