<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Models\Company;
use Modules\Core\Models\ReportBlock;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Services\ReportBlockService;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test suite for Report Builder field canvas integration.
 *
 * Tests the complete workflow of dragging fields to canvas and saving to JSON.
 */
class ReportBuilderFieldCanvasIntegrationTest extends AbstractAdminPanelTestCase
{
    protected Company $company;

    protected ReportTemplate $template;

    private ReportBlockService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /* Arrange - Create test company, template, and service */
        $this->company  = Company::factory()->create();
        $this->template = ReportTemplate::factory()->create([
            'company_id' => $this->company->id,
        ]);
        $this->service = app(ReportBlockService::class);

        Storage::fake('local');
    }

    #[Test]
    #[Group('feature')]
    public function it_saves_fields_when_configuring_block(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'test_canvas',
            'name'       => 'Test Canvas Block',
            'slug'       => 'test-canvas',
            'filename'   => 'test-canvas',
            'width'      => ReportBlockWidth::FULL,
        ]);

        $fields = [
            ['id' => 'company_name', 'label' => 'Company Name', 'x' => 0, 'y' => 0],
            ['id' => 'company_address', 'label' => 'Company Address', 'x' => 0, 'y' => 50],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $fields);
        $loadedFields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertCount(2, $loadedFields);
        $this->assertEquals('company_name', $loadedFields[0]['id']);
        Storage::disk('local')->assertExists('report_blocks/test-canvas.json');
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_separates_fields_from_block_data_when_saving(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'separate_test',
            'name'       => 'Separate Test Block',
            'slug'       => 'separate-test',
            'filename'   => 'separate-test',
            'width'      => ReportBlockWidth::HALF,
        ]);

        $data = [
            'name'        => 'Updated Name',
            'width'       => 'full',
            'data_source' => 'invoice',
            'fields'      => [
                ['id' => 'field1', 'label' => 'Field 1'],
            ],
        ];

        $fields = $data['fields'];
        unset($data['fields']); // Simulate the action handler behavior

        /* Act */
        $block->update($data);
        $this->service->saveBlockFields($block, $fields);

        /* Assert */
        $block->refresh();
        $this->assertEquals('Updated Name', $block->name);
        $this->assertEquals('full', $block->width->value);
        $loadedFields = $this->service->loadBlockFields($block);
        $this->assertCount(1, $loadedFields);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_handles_empty_fields_array_gracefully(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'empty_fields',
            'name'       => 'Empty Fields Block',
            'slug'       => 'empty-fields',
            'filename'   => 'empty-fields',
            'width'      => ReportBlockWidth::HALF,
        ]);

        $fields = [];

        /* Act */
        $this->service->saveBlockFields($block, $fields);
        $loadedFields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertIsArray($loadedFields);
        $this->assertEmpty($loadedFields);
        Storage::disk('local')->assertExists('report_blocks/empty-fields.json');
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_preserves_field_positions_and_dimensions(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'positioned_fields',
            'name'       => 'Positioned Fields Block',
            'slug'       => 'positioned-fields',
            'filename'   => 'positioned-fields',
            'width'      => ReportBlockWidth::FULL,
        ]);

        $fields = [
            [
                'id'     => 'field1',
                'label'  => 'Field 1',
                'x'      => 10,
                'y'      => 20,
                'width'  => 200,
                'height' => 40,
            ],
            [
                'id'     => 'field2',
                'label'  => 'Field 2',
                'x'      => 220,
                'y'      => 20,
                'width'  => 150,
                'height' => 40,
            ],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $fields);
        $loadedFields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertEquals(10, $loadedFields[0]['x']);
        $this->assertEquals(20, $loadedFields[0]['y']);
        $this->assertEquals(200, $loadedFields[0]['width']);
        $this->assertEquals(40, $loadedFields[0]['height']);
        $this->assertEquals(220, $loadedFields[1]['x']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_loads_existing_fields_when_opening_block_editor(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'existing_fields',
            'name'       => 'Existing Fields Block',
            'slug'       => 'existing-fields',
            'filename'   => 'existing-fields',
            'width'      => ReportBlockWidth::TWO_THIRDS,
        ]);

        $initialFields = [
            ['id' => 'invoice_number', 'label' => 'Invoice Number'],
            ['id' => 'invoice_date', 'label' => 'Invoice Date'],
        ];

        $this->service->saveBlockFields($block, $initialFields);

        /* Act */
        $loadedFields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertCount(2, $loadedFields);
        $this->assertEquals($initialFields, $loadedFields);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_allows_updating_fields_through_multiple_edits(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'multiple_edits',
            'name'       => 'Multiple Edits Block',
            'slug'       => 'multiple-edits',
            'filename'   => 'multiple-edits',
            'width'      => ReportBlockWidth::HALF,
        ]);

        $firstFields = [
            ['id' => 'field1', 'label' => 'Field 1'],
        ];

        $secondFields = [
            ['id' => 'field1', 'label' => 'Field 1'],
            ['id' => 'field2', 'label' => 'Field 2'],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $firstFields);
        $afterFirst = $this->service->loadBlockFields($block);

        $this->service->saveBlockFields($block, $secondFields);
        $afterSecond = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertCount(1, $afterFirst);
        $this->assertCount(2, $afterSecond);
        $this->assertEquals('field2', $afterSecond[1]['id']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_handles_complex_field_metadata(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'complex_fields',
            'name'       => 'Complex Fields Block',
            'slug'       => 'complex-fields',
            'filename'   => 'complex-fields',
            'width'      => ReportBlockWidth::FULL,
        ]);

        $fields = [
            [
                'id'     => 'styled_field',
                'label'  => 'Styled Field',
                'x'      => 0,
                'y'      => 0,
                'width'  => 200,
                'height' => 40,
                'style'  => [
                    'color'      => '#ff0000',
                    'fontSize'   => 14,
                    'fontWeight' => 'bold',
                    'textAlign'  => 'center',
                ],
                'visible'  => true,
                'required' => false,
            ],
        ];

        /* Act */
        $this->service->saveBlockFields($block, $fields);
        $loadedFields = $this->service->loadBlockFields($block);

        /* Assert */
        $this->assertArrayHasKey('style', $loadedFields[0]);
        $this->assertEquals('#ff0000', $loadedFields[0]['style']['color']);
        $this->assertEquals(14, $loadedFields[0]['style']['fontSize']);
        $this->assertTrue($loadedFields[0]['visible']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_works_with_all_block_width_types(): void
    {
        /* Arrange */
        $widths = [
            ReportBlockWidth::ONE_THIRD,
            ReportBlockWidth::HALF,
            ReportBlockWidth::TWO_THIRDS,
            ReportBlockWidth::FULL,
        ];

        $blocks = [];
        foreach ($widths as $width) {
            $blocks[] = ReportBlock::factory()->create([
                'block_type' => 'width_' . $width->value,
                'name'       => ucfirst($width->value) . ' Block',
                'slug'       => 'width-' . str_replace('_', '-', $width->value),
                'filename'   => 'width-' . str_replace('_', '-', $width->value),
                'width'      => $width,
            ]);
        }

        $fields = [
            ['id' => 'test_field', 'label' => 'Test Field'],
        ];

        /* Act & Assert */
        foreach ($blocks as $block) {
            $this->service->saveBlockFields($block, $fields);
            $loadedFields = $this->service->loadBlockFields($block);
            $this->assertCount(1, $loadedFields);
            $this->assertEquals('test_field', $loadedFields[0]['id']);
        }
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }
}
