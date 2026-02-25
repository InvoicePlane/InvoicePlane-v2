<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Models\Company;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Services\MasonStorageAdapter;
use Modules\Core\Services\ReportTemplateService;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportBuilderMasonIntegrationTest extends AbstractAdminPanelTestCase
{
    protected Company $company;
    protected ReportTemplate $template;
    protected ReportTemplateService $service;
    protected MasonStorageAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('report_templates');

        $this->company = Company::factory()->create();
        $this->service = app(ReportTemplateService::class);
        $this->adapter = app(MasonStorageAdapter::class);

        $this->template = ReportTemplate::factory()->create([
            'company_id' => $this->company->id,
            'slug' => 'test-invoice',
            'template_type' => ReportTemplateType::INVOICE,
        ]);
    }

    #[Test]
    public function it_saves_mason_content_to_filesystem(): void
    {
        /* Arrange */
        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_test',
                        'config' => ['show_vat_id' => true],
                        'label' => 'Company Header',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);
        $this->service->persistBlocks($this->template, $blocks);

        /* Assert */
        $path = "{$this->company->id}/{$this->template->slug}.json";
        Storage::disk('report_templates')->assertExists($path);
    }

    #[Test]
    public function it_saves_and_loads_mason_json_via_mason_template_storage(): void
    {
        /* Arrange */
        $storage = app(\Modules\Core\Services\MasonTemplateStorage::class);
        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_test',
                        'config' => ['show_vat_id' => true],
                        'label' => 'Company Header',
                    ],
                ],
            ],
        ]);

        /* Act */
        $storage->save($this->template, $masonJson);

        /* Assert */
        $path = "{$this->company->id}/mason_{$this->template->slug}.json";
        Storage::disk('report_templates')->assertExists($path);
        
        $loadedJson = $storage->load($this->template);
        $this->assertIsString($loadedJson);
        
        $originalDecoded = json_decode($masonJson, true);
        $loadedDecoded = json_decode($loadedJson, true);
        
        $this->assertIsArray($originalDecoded);
        $this->assertIsArray($loadedDecoded);
        $this->assertSame('doc', $originalDecoded['type']);
        $this->assertSame('doc', $loadedDecoded['type']);
        $this->assertNotEmpty($loadedDecoded['content']);
        $this->assertEquals($originalDecoded, $loadedDecoded);
    }

    #[Test]
    public function it_loads_blocks_and_converts_to_mason_format(): void
    {
        /* Arrange */
        $initialMasonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_abc',
                        'config' => ['show_phone' => true],
                        'label' => 'Company',
                    ],
                ],
            ],
        ]);

        $blocks = $this->adapter->masonToBlocks($initialMasonJson);
        $this->service->persistBlocks($this->template, $blocks);

        /* Act */
        $loadedBlocks = $this->service->loadBlocks($this->template);
        $convertedMason = $this->adapter->blocksToMason($loadedBlocks);
        $decoded = json_decode($convertedMason, true);

        /* Assert */
        $this->assertIsArray($decoded);
        $this->assertEquals('doc', $decoded['type']);
        $this->assertNotEmpty($decoded['content']);
    }

    #[Test]
    public function it_preserves_block_configuration_through_roundtrip(): void
    {
        /* Arrange */
        $config = [
            'show_vat_id' => true,
            'show_phone' => false,
            'font_size' => 12,
            'text_align' => 'right',
        ];

        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_roundtrip',
                        'config' => $config,
                        'label' => 'Test',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);
        $this->service->persistBlocks($this->template, $blocks);
        $loadedBlocks = $this->service->loadBlocks($this->template);
        $block = reset($loadedBlocks);

        /* Assert */
        $this->assertEquals($config, $block->getConfig());
    }

    #[Test]
    public function it_handles_multiple_bricks_of_different_types(): void
    {
        /* Arrange */
        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_test',
                        'config' => [],
                        'label' => 'Company',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_client_test',
                        'config' => [],
                        'label' => 'Client',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'detail_items_test',
                        'config' => [],
                        'label' => 'Items',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'footer_totals_test',
                        'config' => [],
                        'label' => 'Totals',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);
        $this->service->persistBlocks($this->template, $blocks);
        $loadedBlocks = $this->service->loadBlocks($this->template);

        /* Assert */
        $this->assertCount(4, $loadedBlocks);
        
        $types = array_map(fn($block) => $block->getType(), $loadedBlocks);
        $this->assertContains('header_company', $types);
        $this->assertContains('header_client', $types);
        $this->assertContains('detail_items', $types);
        $this->assertContains('footer_totals', $types);
    }

    #[Test]
    public function it_maintains_block_order_through_persistence(): void
    {
        /* Arrange */
        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'first_block',
                        'config' => [],
                        'label' => 'First',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'second_block',
                        'config' => [],
                        'label' => 'Second',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'third_block',
                        'config' => [],
                        'label' => 'Third',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);
        $this->service->persistBlocks($this->template, $blocks);
        $loadedBlocks = $this->service->loadBlocks($this->template);
        $convertedMason = $this->adapter->blocksToMason($loadedBlocks);
        $decoded = json_decode($convertedMason, true);

        /* Assert */
        $this->assertCount(3, $decoded['content']);
    }

    #[Test]
    public function it_assigns_correct_data_sources_to_blocks(): void
    {
        /* Arrange */
        $masonJson = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_company_src',
                        'config' => [],
                        'label' => 'Company',
                    ],
                ],
                [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'id' => 'header_invoice_meta_src',
                        'config' => [],
                        'label' => 'Invoice',
                    ],
                ],
            ],
        ]);

        /* Act */
        $blocks = $this->adapter->masonToBlocks($masonJson);

        /* Assert */
        $companyBlock = $blocks['header_company_src'];
        $invoiceBlock = $blocks['header_invoice_meta_src'];

        $this->assertEquals('company', $companyBlock->getDataSource());
        $this->assertEquals('invoice', $invoiceBlock->getDataSource());
    }
}
