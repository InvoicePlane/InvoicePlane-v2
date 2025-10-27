<?php

namespace Modules\Expenses\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\ListExpenses;
use Modules\Expenses\Models\Expense;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ExpensesExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_exports_expenses_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $expenses = Expense::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('exportCsvV2')
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
        $this->assertCount($expenses->count() + 1, $lines);
        foreach ($expenses as $expense) {
            $this->assertStringContainsString((string) $expense->amount, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_expenses_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $expenses = Expense::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
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
    public function it_exports_expenses_with_no_records(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        // No expenses created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('exportExcelV2')
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
    public function it_exports_expenses_with_special_characters(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $expenses = Expense::factory()->for($this->company)->create(['description' => 'Üxpense, "Test"', 'amount' => 123.45]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('exportCsv')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertMatchesRegularExpression('/^text\/csv\b/i', $response->headers->get('content-type'));
        $content = $response->getContent();
        $this->assertStringContainsString('Üxpense', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('123.45', $content);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_expenses_downloads_csv_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $expenses = Expense::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('exportCsvV2')
            ->callMountedAction();
        $response = $component->lastResponse;
        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($expenses->count() + 1, $lines);
        foreach ($expenses as $expense) {
            $this->assertStringContainsString((string) $expense->amount, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_expenses_downloads_csv_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $expenses = Expense::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->mountAction('exportCsvV1')
            ->callMountedAction();
        $response = $component->lastResponse;
        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertTrue(in_array($response->headers->get('content-type'), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']));
        $content = $response->getContent();
        $lines   = preg_split('/\r?\n/', mb_trim($content));
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertCount($expenses->count() + 1, $lines);
        foreach ($expenses as $expense) {
            $this->assertStringContainsString((string) $expense->amount, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_expenses_downloads_excel_with_correct_data_v2(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $expenses = Expense::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
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
    public function it_exports_expenses_downloads_excel_with_correct_data_v1(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $expenses = Expense::factory()->for($this->company)->count(3)->create();
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
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
