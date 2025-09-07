<?php

namespace Modules\Products\Feature\Modules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Products\Filament\Company\Resources\Products\Pages\ListProducts;
use Modules\Products\Models\Product;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ProductsExportImportTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('export')]
    public function export_products_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $products = Product::factory()->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
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
        $this->assertCount($products->count() + 1, $lines);
        foreach ($products as $product) {
            $this->assertStringContainsString($product->name, $content);
        }
    }

    #[Test]
    #[Group('import')]
    public function import_products_creates_records_from_csv(): void
    {
        /* Arrange */
        $csv  = "name,sku\nTest Product,SKU001\nAnother Product,SKU002\n";
        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', ['name' => 'Test Product', 'sku' => 'SKU001']);
        $this->assertDatabaseHas('products', ['name' => 'Another Product', 'sku' => 'SKU002']);
    }
}
