<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Modules\Core\Commands\ExportFormDbSchemaCommand;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * mind-the-gap:export-schema is the ground truth the whole E2E
 * required-field generator (required-field-helpers.js) consumes at
 * collection time. A rename of a JSON key or a resource whose
 * getModel()/getSlug() throws fails no other PHPUnit test and only
 * surfaces opaquely at Playwright collection.
 */
#[CoversClass(ExportFormDbSchemaCommand::class)]
class ExportFormDbSchemaCommandTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_exports_a_stable_schema_json_shape_for_every_filament_resource(): void
    {
        /* Act */
        $exit   = Artisan::call('mind-the-gap:export-schema');
        $output = Artisan::output();

        /* Assert */
        $this->assertSame(0, $exit);

        $json = json_decode($output, true);
        $this->assertIsArray($json, 'command output is not valid JSON');
        $this->assertArrayHasKey('generatedAt', $json);
        $this->assertArrayHasKey('resources', $json);
        $this->assertArrayHasKey('knownGaps', $json);
        $this->assertNotEmpty($json['resources']);

        $taxRates = collect($json['resources'])->firstWhere('slug', 'tax-rates');
        $this->assertNotNull($taxRates, 'admin tax-rates resource missing from the export');
        $this->assertSame('admin', $taxRates['panel']);
        $this->assertSame('tax_rates', $taxRates['table']);

        $column = collect($taxRates['columns'])->firstWhere('name', 'code');
        $this->assertNotNull($column, 'tax_rates.code missing — the E2E generator keys off these');
        foreach (['name', 'nullable', 'default', 'auto_increment'] as $key) {
            $this->assertArrayHasKey($key, $column, "column entry lost its '{$key}' key");
        }
    }
}
