<?php

namespace Modules\Core\Tests\Unit\Services\Import;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Company;
use Modules\Core\Services\Import\ProductsImportService;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\Test;

class ProductsImportServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private ProductsImportService $service;

    private $company;

    private array $idMappings = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->service    = new ProductsImportService();
        $this->company    = Company::factory()->create();
        $this->idMappings = ['tax_rates' => [], 'product_families' => [], 'product_units' => []];

        DB::purge('import_v1');

        $this->setupImportDatabase();
    }

    protected function tearDown(): void
    {
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_families');
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_units');
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_products');
        parent::tearDown();
    }

    #[Test]
    public function it_imports_product_categories_successfully(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_families')->insert([
            ['family_id' => 1, 'family_name' => 'Services'],
            ['family_id' => 2, 'family_name' => 'Products'],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(2, $stats['product_categories']);
        $this->assertDatabaseHas('product_categories', ['company_id' => $this->company->id, 'category_name' => 'Services']);
        $this->assertDatabaseHas('product_categories', ['company_id' => $this->company->id, 'category_name' => 'Products']);
        $this->assertArrayHasKey(1, $this->idMappings['product_families']);
        $this->assertArrayHasKey(2, $this->idMappings['product_families']);
    }

    #[Test]
    public function it_imports_product_units_successfully(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_units')->insert([
            ['unit_id' => 1, 'unit_name' => 'Hour', 'unit_name_plrl' => 'Hours'],
            ['unit_id' => 2, 'unit_name' => 'Piece', 'unit_name_plrl' => 'Pieces'],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(2, $stats['product_units']);
        $this->assertDatabaseHas('product_units', ['company_id' => $this->company->id, 'unit_name' => 'Hour']);
        $this->assertDatabaseHas('product_units', ['company_id' => $this->company->id, 'unit_name' => 'Piece']);
    }

    #[Test]
    public function it_imports_products_with_all_relationships(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_families')->insert([
            ['family_id' => 1, 'family_name' => 'Services'],
        ]);
        DB::connection('import_v1')->table('ip_units')->insert([
            ['unit_id' => 1, 'unit_name' => 'Hour', 'unit_name_plrl' => 'Hours'],
        ]);
        DB::connection('import_v1')->table('ip_products')->insert([
            [
                'product_id'          => 1,
                'family_id'           => 1,
                'unit_id'             => 1,
                'tax_rate_id'         => null,
                'product_sku'         => 'SRV001',
                'product_name'        => 'Consulting',
                'product_description' => 'Hourly consulting',
                'product_price'       => 100.00,
            ],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(1, $stats['products']);

        $product = Product::where('company_id', $this->company->id)->first();
        $this->assertNotNull($product);
        $this->assertEquals('Consulting', $product->product_name);
        $this->assertEquals('SRV001', $product->code);
        $this->assertEquals(100.00, $product->price);
        $this->assertNotNull($product->category_id);
        $this->assertNotNull($product->unit_id);
    }

    #[Test]
    public function it_creates_default_category_when_family_not_found(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_products')->insert([
            [
                'product_id'          => 1,
                'family_id'           => 999, // Non-existent
                'unit_id'             => null,
                'tax_rate_id'         => null,
                'product_sku'         => null,
                'product_name'        => 'Test Product',
                'product_description' => null,
                'product_price'       => 50.00,
            ],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(1, $stats['products']);

        $product = Product::where('company_id', $this->company->id)->first();
        $this->assertNotNull($product);

        $defaultCategory = ProductCategory::where('company_id', $this->company->id)
            ->where('category_name', 'Default')
            ->first();
        $this->assertNotNull($defaultCategory);
        $this->assertEquals($defaultCategory->id, $product->category_id);
    }

    #[Test]
    public function it_handles_unit_name_plural_fallback(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_units')->insert([
            ['unit_id' => 1, 'unit_name' => 'Item', 'unit_name_plrl' => null],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $unit = ProductUnit::where('company_id', $this->company->id)->where('unit_name', 'Item')->first();
        $this->assertNotNull($unit);
        $this->assertEquals('Item', $unit->unit_name_plrl);
    }

    #[Test]
    public function it_returns_correct_table_list(): void
    {
        /* Assert */
        $expected = ['ip_families', 'ip_units', 'ip_products'];
        $this->assertEquals($expected, $this->service->getTables());
    }

    private function setupImportDatabase(): void
    {
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_families');
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_units');
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_products');

        DB::connection('import_v1')->statement('
            CREATE TABLE ip_families (
                family_id INT PRIMARY KEY,
                family_name VARCHAR(255)
            )
        ');

        DB::connection('import_v1')->statement('
            CREATE TABLE ip_units (
                unit_id INT PRIMARY KEY,
                unit_name VARCHAR(255),
                unit_name_plrl VARCHAR(255)
            )
        ');

        DB::connection('import_v1')->statement('
            CREATE TABLE ip_products (
                product_id INT PRIMARY KEY,
                family_id INT,
                unit_id INT,
                tax_rate_id INT,
                product_sku VARCHAR(255),
                product_name VARCHAR(255),
                product_description TEXT,
                product_price DECIMAL(20,4)
            )
        ');
    }
}
