<?php

namespace Modules\Core\Tests\Unit\Services\Import;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Services\Import\TaxRatesImportService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class TaxRatesImportServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private TaxRatesImportService $service;

    private $company;

    private array $idMappings = [];

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * These tests exercise the V1 import against a real import_v1
         * database; skip when none is reachable (e.g. sqlite CI env).
         */
        try {
            DB::connection('import_v1')->getPdo();
        } catch (Throwable $e) {
            $this->markTestSkipped('import_v1 database connection unavailable');
        }

        $this->service = new TaxRatesImportService();
        $this->company = Company::factory()->create();

        DB::purge('import_v1');

        $this->setupImportDatabase();
    }

    protected function tearDown(): void
    {
        try {
            DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_tax_rates');
        } catch (Throwable) {
            // connection unavailable — nothing to drop
        }
        parent::tearDown();
    }

    #[Test]
    public function it_imports_tax_rates_successfully(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_tax_rates')->insert([
            ['tax_rate_id' => 1, 'tax_rate_name' => 'VAT 21%', 'tax_rate_percent' => 21.000],
            ['tax_rate_id' => 2, 'tax_rate_name' => 'VAT 9%', 'tax_rate_percent' => 9.000],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(2, $stats['tax_rates']);
        $this->assertDatabaseHas('tax_rates', ['company_id' => $this->company->id, 'name' => 'VAT 21%']);
        $this->assertDatabaseHas('tax_rates', ['company_id' => $this->company->id, 'name' => 'VAT 9%']);

        $taxRate1 = TaxRate::where('company_id', $this->company->id)
            ->where('name', 'VAT 21%')
            ->first();
        $this->assertNotNull($taxRate1);
        $this->assertEquals(21.000, $taxRate1->rate);
        $this->assertArrayHasKey(1, $this->idMappings['tax_rates']);
    }

    #[Test]
    public function it_handles_missing_tax_rate_name_with_default(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_tax_rates')->insert([
            ['tax_rate_id' => 1, 'tax_rate_name' => null, 'tax_rate_percent' => 21.000],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(1, $stats['tax_rates']);
        $taxRate = TaxRate::where('company_id', $this->company->id)->where('name', 'Tax')->first();
        $this->assertNotNull($taxRate);
        $this->assertEquals('Tax', $taxRate->name);
    }

    #[Test]
    public function it_handles_missing_tax_rate_percent_with_zero(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_tax_rates')->insert([
            ['tax_rate_id' => 1, 'tax_rate_name' => 'VAT', 'tax_rate_percent' => null],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(1, $stats['tax_rates']);
        $taxRate = TaxRate::where('company_id', $this->company->id)->where('name', 'VAT')->first();
        $this->assertNotNull($taxRate);
        $this->assertEquals(0, $taxRate->rate);
    }

    #[Test]
    public function it_handles_empty_table_gracefully(): void
    {
        /* Arrange */
        // Table exists but is empty

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(0, $stats['tax_rates']);
    }

    #[Test]
    public function it_avoids_duplicate_tax_rates(): void
    {
        /* Arrange */
        DB::connection('import_v1')->table('ip_tax_rates')->insert([
            ['tax_rate_id' => 1, 'tax_rate_name' => 'VAT 21%', 'tax_rate_percent' => 21.000],
            ['tax_rate_id' => 2, 'tax_rate_name' => 'VAT 21%', 'tax_rate_percent' => 21.000],
        ]);

        /* Act */
        $stats = $this->service->import($this->company->id, $this->idMappings);

        /* Assert */
        $this->assertEquals(2, $stats['tax_rates']);
        // Should create only 1 unique tax rate due to firstOrCreate
        $this->assertEquals(1, TaxRate::where('company_id', $this->company->id)
            ->where('name', 'VAT 21%')
            ->count());
    }

    #[Test]
    public function it_returns_correct_table_list(): void
    {
        /* Assert */
        $this->assertEquals(['ip_tax_rates'], $this->service->getTables());
    }

    private function setupImportDatabase(): void
    {
        DB::connection('import_v1')->statement('DROP TABLE IF EXISTS ip_tax_rates');
        DB::connection('import_v1')->statement('
            CREATE TABLE ip_tax_rates (
                tax_rate_id INT PRIMARY KEY,
                tax_rate_name VARCHAR(255),
                tax_rate_percent DECIMAL(8,3)
            )
        ');
    }
}
