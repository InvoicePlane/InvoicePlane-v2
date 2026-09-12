<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportsSyncSystemCommandTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ReportTemplateStorage::DISK);
    }

    #[Test]
    public function it_syncs_the_shipped_templates_into_system_storage(): void
    {
        /* Act */
        $this->artisan('reports:sync-system')->assertSuccessful();

        /* Assert */
        $disk = Storage::disk(ReportTemplateStorage::DISK);
        $this->assertTrue($disk->exists('system/invoice/default/manifest.json'));
        $this->assertTrue($disk->exists('system/invoice/default/bands.json'));
        $this->assertTrue($disk->exists('system/quote/default/manifest.json'));
        $this->assertTrue($disk->exists('system/quote/default/bands.json'));
    }

    #[Test]
    public function it_is_idempotent_when_run_twice(): void
    {
        /* Act */
        $this->artisan('reports:sync-system')->assertSuccessful();
        $firstRun = Storage::disk(ReportTemplateStorage::DISK)->allFiles('system');

        $this->artisan('reports:sync-system')->assertSuccessful();
        $secondRun = Storage::disk(ReportTemplateStorage::DISK)->allFiles('system');

        /* Assert */
        $this->assertSame($firstRun, $secondRun);

        $storage = new ReportTemplateStorage();
        $this->assertCount(2, $storage->listSystem());
    }

    #[Test]
    public function it_loads_a_synced_default_template_with_valid_bands(): void
    {
        /* Arrange */
        $this->artisan('reports:sync-system')->assertSuccessful();

        /* Act */
        $storage  = new ReportTemplateStorage();
        $template = $storage->load(
            ReportTemplateStorage::SCOPE_SYSTEM,
            'default',
            \Modules\Core\Enums\ReportTemplateType::INVOICE,
        );

        /* Assert */
        $this->assertNotNull($template);
        $this->assertSame('invoice', $template['manifest']['type']);
        $this->assertNotEmpty($template['bands']['header']);
        $this->assertNotEmpty($template['bands']['details']);
        $this->assertNotEmpty($template['bands']['footer']);
    }
}
