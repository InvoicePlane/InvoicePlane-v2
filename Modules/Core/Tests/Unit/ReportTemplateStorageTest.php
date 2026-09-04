<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class ReportTemplateStorageTest extends AbstractTestCase
{
    protected ReportTemplateStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ReportTemplateStorage::DISK);

        $this->storage = new ReportTemplateStorage();

        session()->put('current_company_id', 1);
    }

    #[Test]
    public function it_round_trips_a_saved_template_through_load(): void
    {
        /* Act */
        $this->storage->save(ReportTemplateStorage::SCOPE_COMPANY, 'test-template', $this->manifest(), $this->bands());
        $loaded = $this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, 'test-template');

        /* Assert */
        $this->assertNotNull($loaded);
        $this->assertSame('Test Template', $loaded['manifest']['name']);
        $this->assertSame('header_company', $loaded['bands']['header'][0]['brick']);
        $this->assertSame(['show_vat_id' => false], $loaded['bands']['header'][0]['config']);
        $this->assertSame('detail_items', $loaded['bands']['details'][0]['brick']);
    }

    #[Test]
    public function it_skips_unknown_bricks_when_sanitizing_bands(): void
    {
        /* Act */
        $sanitized = $this->storage->sanitizeBands([
            'header' => [
                ['brick' => 'header_company', 'width' => 'half', 'config' => []],
                ['brick' => 'evil_unknown_brick', 'width' => 'half', 'config' => []],
            ],
        ]);

        /* Assert */
        $this->assertCount(1, $sanitized['header']);
        $this->assertSame('header_company', $sanitized['header'][0]['brick']);
    }

    #[Test]
    public function it_skips_bricks_placed_in_a_disallowed_band(): void
    {
        /* Act */
        $sanitized = $this->storage->sanitizeBands([
            'header' => [['brick' => 'detail_items', 'width' => 'full', 'config' => []]],
        ]);

        /* Assert */
        $this->assertSame([], $sanitized['header']);
    }

    #[Test]
    public function it_falls_back_to_full_width_for_invalid_widths(): void
    {
        /* Act */
        $sanitized = $this->storage->sanitizeBands([
            'header' => [['brick' => 'header_company', 'width' => 'gigantic', 'config' => []]],
        ]);

        /* Assert */
        $this->assertSame('full', $sanitized['header'][0]['width']);
    }

    #[Test]
    public function it_filters_brick_config_against_the_brick_schema(): void
    {
        /* Act */
        $sanitized = $this->storage->sanitizeBands([
            'header' => [[
                'brick'  => 'header_company',
                'width'  => 'half',
                'config' => ['show_vat_id' => true, 'malicious_key' => '<script>'],
            ]],
        ]);

        /* Assert */
        $this->assertArrayHasKey('show_vat_id', $sanitized['header'][0]['config']);
        $this->assertArrayNotHasKey('malicious_key', $sanitized['header'][0]['config']);
    }

    #[Test]
    public function it_prunes_bricks_that_do_not_apply_to_the_given_type_when_sanitizing(): void
    {
        /* Act */
        $sanitized = $this->storage->sanitizeBands([
            'header' => [
                ['brick' => 'header_invoice_meta', 'width' => 'full', 'config' => []],
                ['brick' => 'header_quote_meta', 'width' => 'full', 'config' => []],
                ['brick' => 'header_company', 'width' => 'full', 'config' => []],
            ],
        ], ReportTemplateType::QUOTE);

        /* Assert */
        $bricks = array_column($sanitized['header'], 'brick');
        $this->assertNotContains('header_invoice_meta', $bricks);
        $this->assertContains('header_quote_meta', $bricks);
        $this->assertContains('header_company', $bricks, 'Untyped bricks must survive sanitizing for every type.');
    }

    #[Test]
    public function it_does_not_filter_by_type_when_no_type_is_given(): void
    {
        /* Act */
        $sanitized = $this->storage->sanitizeBands([
            'header' => [
                ['brick' => 'header_invoice_meta', 'width' => 'full', 'config' => []],
                ['brick' => 'header_quote_meta', 'width' => 'full', 'config' => []],
            ],
        ]);

        /* Assert */
        $bricks = array_column($sanitized['header'], 'brick');
        $this->assertContains('header_invoice_meta', $bricks);
        $this->assertContains('header_quote_meta', $bricks);
    }

    #[Test]
    public function it_always_returns_all_five_bands_after_sanitizing(): void
    {
        /* Act */
        $sanitized = $this->storage->sanitizeBands([]);

        /* Assert */
        $this->assertSame(
            ['header', 'group_header', 'details', 'group_footer', 'footer'],
            array_keys($sanitized),
        );
    }

    #[Test]
    public function it_rejects_path_traversal_slugs(): void
    {
        /* Assert */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        $this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, '../2/stolen');
    }

    #[Test]
    public function it_rejects_slugs_with_invalid_characters(): void
    {
        /* Assert */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        $this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, 'Bad_Slug!');
    }

    #[Test]
    public function it_cannot_load_another_companys_template(): void
    {
        /* Arrange — company 1 owns a template */
        $this->storage->save(ReportTemplateStorage::SCOPE_COMPANY, 'mine', $this->manifest(['slug' => 'mine']), $this->bands());

        /* Act — switch tenant context to company 2 */
        session()->put('current_company_id', 2);
        $loaded = $this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, 'mine');

        /* Assert */
        $this->assertNull($loaded);
        $this->assertSame([], $this->storage->listCompany());
    }

    #[Test]
    public function it_clones_a_system_template_into_the_company_scope(): void
    {
        /* Arrange */
        $this->storage->save(
            ReportTemplateStorage::SCOPE_SYSTEM,
            'default',
            $this->manifest(['name' => 'Default Invoice', 'slug' => 'default']),
            $this->bands(),
            ReportTemplateType::INVOICE,
        );

        /* Act */
        $clone = $this->storage->clone(
            ReportTemplateStorage::SCOPE_SYSTEM,
            'default',
            'My Fancy Invoice',
            ReportTemplateType::INVOICE,
        );

        /* Assert */
        $this->assertSame(ReportTemplateStorage::SCOPE_COMPANY, $clone['scope']);
        $this->assertSame('my-fancy-invoice', $clone['slug']);
        $this->assertSame('My Fancy Invoice', $clone['manifest']['name']);
        $this->assertSame('system/invoice/default', $clone['manifest']['cloned_from']);

        $loaded = $this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, 'my-fancy-invoice');
        $this->assertSame('header_company', $loaded['bands']['header'][0]['brick']);
    }

    #[Test]
    public function it_suffixes_the_slug_when_cloning_to_an_existing_name(): void
    {
        /* Arrange */
        $this->storage->save(
            ReportTemplateStorage::SCOPE_SYSTEM,
            'default',
            $this->manifest(['slug' => 'default']),
            $this->bands(),
            ReportTemplateType::INVOICE,
        );
        $this->storage->clone(ReportTemplateStorage::SCOPE_SYSTEM, 'default', 'Copy', ReportTemplateType::INVOICE);

        /* Act */
        $second = $this->storage->clone(ReportTemplateStorage::SCOPE_SYSTEM, 'default', 'Copy', ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertSame('copy-2', $second['slug']);
    }

    #[Test]
    public function it_renames_a_template_without_changing_its_slug(): void
    {
        /* Arrange */
        $this->storage->save(ReportTemplateStorage::SCOPE_COMPANY, 'test-template', $this->manifest(), $this->bands());

        /* Act */
        $this->storage->rename(ReportTemplateStorage::SCOPE_COMPANY, 'test-template', 'Renamed');
        $loaded = $this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, 'test-template');

        /* Assert */
        $this->assertSame('Renamed', $loaded['manifest']['name']);
    }

    #[Test]
    public function it_refuses_to_rename_a_system_default_template(): void
    {
        /* Arrange */
        $this->storage->save(
            ReportTemplateStorage::SCOPE_SYSTEM,
            'default',
            $this->manifest(['slug' => 'default']),
            $this->bands(),
            ReportTemplateType::INVOICE,
        );

        /* Assert */
        $this->expectException(RuntimeException::class);

        /* Act */
        $this->storage->rename(ReportTemplateStorage::SCOPE_SYSTEM, 'default', 'Hacked Name', ReportTemplateType::INVOICE);
    }

    #[Test]
    public function it_deletes_a_company_template(): void
    {
        /* Arrange */
        $this->storage->save(ReportTemplateStorage::SCOPE_COMPANY, 'test-template', $this->manifest(), $this->bands());

        /* Act */
        $deleted = $this->storage->delete(ReportTemplateStorage::SCOPE_COMPANY, 'test-template');

        /* Assert */
        $this->assertTrue($deleted);
        $this->assertNull($this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, 'test-template'));
    }

    #[Test]
    public function it_refuses_to_delete_a_system_default_template(): void
    {
        /* Assert */
        $this->expectException(RuntimeException::class);

        /* Act */
        $this->storage->delete(ReportTemplateStorage::SCOPE_SYSTEM, 'default', ReportTemplateType::INVOICE);
    }

    #[Test]
    public function it_lists_system_templates_by_type(): void
    {
        /* Arrange */
        $this->storage->save(ReportTemplateStorage::SCOPE_SYSTEM, 'default', $this->manifest(['slug' => 'default']), $this->bands(), ReportTemplateType::INVOICE);
        $this->storage->save(ReportTemplateStorage::SCOPE_SYSTEM, 'default', $this->manifest(['slug' => 'default', 'type' => 'quote']), $this->bands(), ReportTemplateType::QUOTE);

        /* Act & Assert */
        $this->assertCount(2, $this->storage->listSystem());
        $this->assertCount(1, $this->storage->listSystem(ReportTemplateType::INVOICE));
        $this->assertSame('invoice', $this->storage->listSystem(ReportTemplateType::INVOICE)[0]['type']);
    }

    protected function manifest(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'Test Template',
            'slug'        => 'test-template',
            'type'        => 'invoice',
            'version'     => 1,
            'cloned_from' => null,
        ], $overrides);
    }

    protected function bands(): array
    {
        return [
            'header'       => [['brick' => 'header_company', 'width' => 'half', 'config' => ['show_vat_id' => false]]],
            'group_header' => [],
            'details'      => [['brick' => 'detail_items', 'width' => 'full', 'config' => []]],
            'group_footer' => [],
            'footer'       => [['brick' => 'footer_totals', 'width' => 'full', 'config' => []]],
        ];
    }
}
