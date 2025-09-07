<?php

namespace Modules\Payments\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Payments\Filament\Company\Resources\Payments\Pages\ListPayments;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class PaymentsExportImportTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function export_payments_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $payments = Payment::factory()->count(3)->create();

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
    #[Group('import')]
    public function import_payments_creates_records_from_csv(): void
    {
        /* Arrange */
        $csv  = "amount,reference\n100.00,REF-1001\n200.00,REF-1002\n";
        $file = UploadedFile::fake()->createWithContent('payments.csv', $csv);

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
