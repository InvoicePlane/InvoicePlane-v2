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
    public function export_products_downloads_csv_with_correct_data(): void
    {
        /* Arrange */
        $products = Product::factory()->for($this->company)->count(3)->create();

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
    #[Group('export')]
    public function export_products_downloads_excel_with_correct_data(): void
    {
        /* Arrange */
        $products = Product::factory()->for($this->company)->count(3)->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
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
    public function export_products_with_no_records(): void
    {
        /* Arrange */
        // No products created

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
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
    public function export_products_with_special_characters(): void
    {
        /* Arrange */
        $products = Product::factory()->for($this->company)->create(['name' => 'Prødüct, "Test"', 'sku' => 'special-sku']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('export')
            ->callMountedAction();
        $response = $component->lastResponse;

        /* Assert */
        $this->assertEquals(200, $response->status());
        $content = $response->getContent();
        $this->assertStringContainsString('Prødüct', $content);
        $this->assertStringContainsString('"Test"', $content);
        $this->assertStringContainsString('special-sku', $content);
    }

    #[Test]
    #[Group('import')]
    public function import_products_with_empty_file(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', '');

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('products', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_products_with_only_headers(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', "name,sku\n");

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('products', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_products_with_invalid_columns(): void
    {
        /* Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', "foo,bar\nabc,def\n");

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('products', 0);
    }

    #[Test]
    #[Group('import')]
    public function import_products_with_duplicate_records(): void
    {
        /* Arrange */
        $csv  = "name,sku\nDup Product,dup-sku\nDup Product,dup-sku\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('products', 2);
    }

    #[Test]
    #[Group('import')]
    public function import_products_with_invalid_data_types(): void
    {
        /* Arrange */
        $csv  = "name,sku\n12345,not-a-sku\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csv);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseHas('products', ['name' => '12345', 'sku' => 'not-a-sku']);
    }

    #[Test]
    #[Group('import')]
    public function import_products_with_large_file(): void
    {
        /* Arrange */
        $rows = [];
        for ($i = 0; $i < 1000; $i++) {
            $rows[] = "Product{$i},sku{$i}";
        }
        $csv  = "name,sku\n" . implode("\n", $rows);
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('products', 1000);
    }

    #[Test]
    #[Group('import')]
    public function import_products_with_extra_columns(): void
    {
        /* Arrange */
        $csv  = "name,sku,extra\nExtra Product,extra-sku,something\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csv);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseHas('products', ['name' => 'Extra Product', 'sku' => 'extra-sku']);
    }

    #[Test]
    #[Group('import')]
    public function import_products_with_missing_required_columns(): void
    {
        /* Arrange */
        $csv  = "name\nMissing SKU\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csv);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction('import')
            ->set('data.file', $file)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseCount('products', 0);
    }
}
