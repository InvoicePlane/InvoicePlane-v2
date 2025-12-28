<?php

namespace Modules\Expenses\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
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
    public function it_dispatches_csv_export_job_v2(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $expenses = Expense::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'expense_status' => ['isEnabled' => true, 'label' => 'Status'],
                    'expense_number' => ['isEnabled' => true, 'label' => 'Number'],
                    'expense_amount' => ['isEnabled' => true, 'label' => 'Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_excel_export_job_v2(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $expenses = Expense::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'expense_status' => ['isEnabled' => true, 'label' => 'Status'],
                    'expense_number' => ['isEnabled' => true, 'label' => 'Number'],
                    'expense_amount' => ['isEnabled' => true, 'label' => 'Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_with_no_records(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        // No expenses created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'expense_number' => ['isEnabled' => true, 'label' => 'Number'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_exports_with_special_characters(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $expense = Expense::factory()->for($this->company)->create([
            'description' => 'Üxpense, "Test"',
            'amount' => 123.45,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'expense_number' => ['isEnabled' => true, 'label' => 'Number'],
                    'expense_amount' => ['isEnabled' => true, 'label' => 'Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v2_with_column_selection(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $expenses = Expense::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'expense_status' => ['isEnabled' => true, 'label' => 'Status'],
                    'expense_number' => ['isEnabled' => true, 'label' => 'Number'],
                    'expense_amount' => ['isEnabled' => false, 'label' => 'Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v1(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $expenses = Expense::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'expense_number' => ['isEnabled' => true, 'label' => 'Number'],
                    'expense_amount' => ['isEnabled' => true, 'label' => 'Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_excel_export_job_v2_with_data(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $expenses = Expense::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'expense_number' => ['isEnabled' => true, 'label' => 'Number'],
                    'expense_amount' => ['isEnabled' => true, 'label' => 'Amount'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }

    #[Test]
    #[Group('export')]
    public function it_dispatches_excel_export_job_v1(): void
    {
        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $expenses = Expense::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListExpenses::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'expense_number' => ['isEnabled' => true, 'label' => 'Number'],
                ],
            ]);

        /* Assert */
        Bus::assertChained([
            function ($batch) {
                return $batch instanceof \Illuminate\Bus\PendingBatch;
            },
        ]);
    }
}
