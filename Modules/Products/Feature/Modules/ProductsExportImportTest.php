<?php

namespace Modules\Products\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Products\Filament\Company\Resources\Products\Pages\ListProducts;
use Modules\Products\Models\Product;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ProductsExportImportTest extends AbstractCompanyPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function it_dispatches_csv_export_job_v2(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        Bus::fake();
        $products = Product::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('exportCsvV2', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Product Name'],
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
        $this->markTestIncomplete();

        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $products = Product::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Product Name'],
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
        $this->markTestIncomplete();

        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        // No products created

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Product Name'],
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
        $this->markTestIncomplete();

        /* Arrange */
        Queue::fake();
        Storage::fake('local');
        $product = Product::factory()->for($this->company)->create([
            'name' => 'ÜProduct, "Test"',
            'price' => 123.45,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('exportExcelV2', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Product Name'],
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
        $products = Product::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('exportCsvV1', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Product Name'],
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
        $products = Product::factory()->for($this->company)->count(3)->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('exportExcelV1', data: [
                'columnMap' => [
                    'name' => ['isEnabled' => true, 'label' => 'Product Name'],
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
