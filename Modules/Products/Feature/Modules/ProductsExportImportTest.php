<?php

namespace Modules\Products\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
    public function it_exports_products_downloads_csv_with_correct_data(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $products = Product::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('exportCsvV2')
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
        $this->assertCount($products->count() + 1, $lines);
        foreach ($products as $product) {
            $this->assertStringContainsString($product->name, $content);
        }
    }

    #[Test]
    #[Group('export')]
    public function it_exports_products_downloads_excel_with_correct_data(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $products = Product::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
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
    public function it_exports_products_with_no_records(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        // No products created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
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
    public function it_exports_products_with_special_characters(): void
    {
        $this->markTestIncomplete();

        /* Arrange */
        $products = Product::factory()->for($this->company)->create(['name' => 'Prødüct, "Test"', 'sku' => 'special-sku']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('exportExcelV2')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('Prødüct', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('special-sku', $content);
    }
}
