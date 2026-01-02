<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Models\ReportBlock;
use Modules\Core\Services\ReportBlockService;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test suite for ReportBlockService field management.
 *
 * Tests the JSON file-based field configuration storage and retrieval.
 */
class ReportBlockServiceFieldsTest extends AbstractAdminPanelTestCase
{
    private ReportBlockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReportBlockService::class);
        Storage::fake('local');
    }

    #[Test]
    #[Group('unit')]
    public function it_saves_block_fields_to_json_file(): void
    {
        /* Arrange */
        $block = new ReportBlock();
        $block->id = 1;
        $block->block_type = 'test_block';
        $block->name = 'Test Block';
        $block->slug = 'test-block';
        $block->filename = 'test-block';
        $block->width = ReportBlockWidth::FULL;

        $fields = [
            ['id' => 'company_name', 'label' => 'Company Name', 'x' => 0, 'y' => 0],
            ['id' => 'company_address', 'label' => 'Company Address', 'x' => 0, 'y' => 50],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $fields);

        /* Assert */
        Storage::disk('local')->assertExists('report_blocks/test-block.json');
        $content = Storage::disk('local')->get('report_blocks/test-block.json');
        $config = json_decode($content, true);
        $this->assertArrayHasKey('fields', $config);
        $this->assertCount(2, $config['fields']);
        $this->assertEquals('company_name', $config['fields'][0]['id']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_loads_block_fields_from_json_file(): void
    {
        /* Arrange */
        $block = new ReportBlock();
        $block->id = 1;
        $block->block_type = 'test_block';
        $block->name = 'Test Block';
        $block->slug = 'test-block';
        $block->filename = 'test-block';
        $block->width = ReportBlockWidth::FULL;

        $fields = [
            ['id' => 'invoice_number', 'label' => 'Invoice Number', 'x' => 100, 'y' => 0],
            ['id' => 'invoice_date', 'label' => 'Invoice Date', 'x' => 100, 'y' => 50],
        ];

        // Save first
        $this->service->saveBlockFields($block, $fields);

        /* Act */
        $loadedFields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertCount(2, $loadedFields);
        $this->assertEquals('invoice_number', $loadedFields[0]['id']);
        $this->assertEquals('invoice_date', $loadedFields[1]['id']);
        $this->assertEquals(100, $loadedFields[0]['x']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_returns_empty_array_when_json_file_does_not_exist(): void
    {
        /* Arrange */
        $block = new ReportBlock();
        $block->id = 1;
        $block->block_type = 'nonexistent_block';
        $block->name = 'Nonexistent Block';
        $block->slug = 'nonexistent-block';
        $block->filename = 'nonexistent-block';
        $block->width = ReportBlockWidth::HALF;

        /* Act */
        $fields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_directory_if_not_exists_when_saving(): void
    {
        /* Arrange */
        $block = new ReportBlock();
        $block->id = 1;
        $block->block_type = 'new_block';
        $block->name = 'New Block';
        $block->slug = 'new-block';
        $block->filename = 'new-block';
        $block->width = ReportBlockWidth::HALF;

        $fields = [
            ['id' => 'test_field', 'label' => 'Test Field', 'x' => 0, 'y' => 0],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $fields);

        /* Assert */
        Storage::disk('local')->assertExists('report_blocks');
        Storage::disk('local')->assertExists('report_blocks/new-block.json');
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_gets_full_block_configuration_from_json(): void
    {
        /* Arrange */
        $block = new ReportBlock();
        $block->id = 1;
        $block->block_type = 'config_block';
        $block->name = 'Config Block';
        $block->slug = 'config-block';
        $block->filename = 'config-block';
        $block->width = ReportBlockWidth::FULL;

        $fields = [
            ['id' => 'field1', 'label' => 'Field 1'],
            ['id' => 'field2', 'label' => 'Field 2'],
        ];

        $this->service->saveBlockFields($block, $fields);

        /* Act */
        $config = $this->service->getBlockConfiguration($block);

        /* Assert */
        $this->assertIsArray($config);
        $this->assertArrayHasKey('fields', $config);
        $this->assertCount(2, $config['fields']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_uses_slug_as_filename_when_filename_is_null(): void
    {
        /* Arrange */
        $block = new ReportBlock();
        $block->id = 1;
        $block->block_type = 'slug_block';
        $block->name = 'Slug Block';
        $block->slug = 'slug-block';
        $block->filename = null;
        $block->width = ReportBlockWidth::HALF;

        $fields = [
            ['id' => 'test', 'label' => 'Test'],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $fields);

        /* Assert */
        Storage::disk('local')->assertExists('report_blocks/slug-block.json');
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_overwrites_existing_fields_when_saving(): void
    {
        /* Arrange */
        $block = new ReportBlock();
        $block->id = 1;
        $block->block_type = 'overwrite_block';
        $block->name = 'Overwrite Block';
        $block->slug = 'overwrite-block';
        $block->filename = 'overwrite-block';
        $block->width = ReportBlockWidth::FULL;

        $initialFields = [
            ['id' => 'field1', 'label' => 'Field 1'],
            ['id' => 'field2', 'label' => 'Field 2'],
        ];

        $updatedFields = [
            ['id' => 'field3', 'label' => 'Field 3'],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $initialFields);
        $this->service->saveBlockFields($block, $updatedFields);
        $loadedFields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertCount(1, $loadedFields);
        $this->assertEquals('field3', $loadedFields[0]['id']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_preserves_json_structure_when_saving_and_loading(): void
    {
        /* Arrange */
        $block = new ReportBlock();
        $block->id = 1;
        $block->block_type = 'structure_block';
        $block->name = 'Structure Block';
        $block->slug = 'structure-block';
        $block->filename = 'structure-block';
        $block->width = ReportBlockWidth::TWO_THIRDS;

        $fields = [
            [
                'id' => 'complex_field',
                'label' => 'Complex Field',
                'x' => 10,
                'y' => 20,
                'width' => 200,
                'height' => 40,
                'style' => ['color' => 'red', 'fontSize' => 14],
            ],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $fields);
        $loadedFields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertEquals($fields, $loadedFields);
        $this->assertArrayHasKey('style', $loadedFields[0]);
        $this->assertEquals('red', $loadedFields[0]['style']['color']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }
}
