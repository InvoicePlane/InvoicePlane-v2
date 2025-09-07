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
    public function export_payments_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
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
        $this->assertCount($payments->count() + 1, $lines);
        foreach ($payments as $payment) {
            $this->assertStringContainsString((string) $payment->amount, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function export_payments_downloads_excel_with_correct_data(): void
    {
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
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
    public function export_payments_with_no_records(): void
    {
        /* Arrange */
        // No payments created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
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
    public function export_payments_with_special_characters(): void
    {
        /* Arrange */
        $payments = Payment::factory()->for($this->company)->create(['amount' => 123.45, 'reference' => 'REF-Ü, "Test"']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('export')
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
    #[Group('import')]
    public function import_payments_with_empty_file(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('payments.csv', '');

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('payments', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_payments_with_only_headers(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('payments.csv', "amount,reference\n");

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('payments', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_payments_with_invalid_columns(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('payments.csv', "foo,bar\nabc,def\n");

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('payments', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_payments_with_duplicate_records(): void
    {
        /* Arrange */
        $csv  = "amount,reference\n100.00,dup-ref\n100.00,dup-ref\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('payments.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListPayments::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseHas('payments', ['amount' => 100.00, 'reference' => 'REF-1001']);
        $this->assertDatabaseHas('payments', ['amount' => 200.00, 'reference' => 'REF-1002']);
    }
}
