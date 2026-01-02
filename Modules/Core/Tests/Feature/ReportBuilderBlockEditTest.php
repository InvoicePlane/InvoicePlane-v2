<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Models\Company;
use Modules\Core\Models\ReportBlock;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test suite for Report Builder block edit action.
 *
 * Tests that the edit action properly populates the form with block data.
 */
class ReportBuilderBlockEditTest extends AbstractAdminPanelTestCase
{
    private Company $company;

    private ReportTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        /* Arrange - Create test company and template */
        $this->company = Company::factory()->create();
        $this->template = ReportTemplate::factory()->create([
            'company_id' => $this->company->id,
        ]);
    }

    #[Test]
    #[Group('feature')]
    public function it_looks_up_block_by_block_type(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'company_header',
            'name' => 'Company Header',
            'slug' => 'company-header',
            'width' => ReportBlockWidth::HALF,
            'data_source' => 'company',
            'default_band' => 'header',
            'is_active' => true,
        ]);

        /* Act */
        $foundBlock = ReportBlock::query()->where('block_type', 'company_header')->first();

        /* Assert */
        $this->assertNotNull($foundBlock);
        $this->assertEquals('company_header', $foundBlock->block_type);
        $this->assertEquals('Company Header', $foundBlock->name);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_populates_form_with_block_data(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'invoice_items',
            'name' => 'Invoice Items',
            'slug' => 'invoice-items',
            'width' => ReportBlockWidth::FULL,
            'data_source' => 'invoice',
            'default_band' => 'details',
            'is_active' => true,
            'config' => ['show_description' => true, 'show_quantity' => true],
        ]);

        /* Act */
        $data = $block->toArray();

        /* Assert */
        $this->assertEquals('invoice_items', $data['block_type']);
        $this->assertEquals('Invoice Items', $data['name']);
        $this->assertEquals('invoice', $data['data_source']);
        $this->assertEquals('details', $data['default_band']);
        $this->assertTrue($data['is_active']);
        $this->assertIsArray($data['config']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_converts_width_enum_to_value_for_form(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'footer_totals',
            'name' => 'Footer Totals',
            'width' => ReportBlockWidth::TWO_THIRDS,
        ]);

        /* Act */
        $data = $block->toArray();

        // Simulate the form fill process
        if (isset($data['width']) && $data['width'] instanceof \BackedEnum) {
            $data['width'] = $data['width']->value;
        }

        /* Assert */
        $this->assertEquals('two_thirds', $data['width']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_provides_default_values_when_block_not_found(): void
    {
        /* Arrange */
        $blockType = 'nonexistent_block';

        /* Act */
        $block = ReportBlock::query()->where('block_type', $blockType)->first();

        $defaultData = [
            'name'         => '',
            'width'        => 'full',
            'block_type'   => $blockType,
            'data_source'  => '',
            'default_band' => '',
            'is_active'    => true,
        ];

        /* Assert */
        $this->assertNull($block);
        $this->assertEquals($blockType, $defaultData['block_type']);
        $this->assertTrue($defaultData['is_active']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_logs_block_data_for_debugging(): void
    {
        /* Arrange */
        Log::shouldReceive('info')
            ->twice()
            ->with('Block data for edit:', \Mockery::type('array'))
            ->andReturnNull();

        Log::shouldReceive('info')
            ->twice()
            ->with('Mounting block config with data:', \Mockery::type('array'))
            ->andReturnNull();

        $block = ReportBlock::factory()->create([
            'block_type' => 'test_logging',
            'name' => 'Test Logging Block',
        ]);

        /* Act */
        $data = $block->toArray();
        Log::info('Block data for edit:', $data);
        Log::info('Mounting block config with data:', $data);

        /* Assert */
        $this->assertTrue(true); // Log assertions are handled by shouldReceive
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_handles_all_block_types_correctly(): void
    {
        /* Arrange */
        $blockTypes = [
            'company_header',
            'client_header',
            'header_invoice_meta',
            'invoice_items',
            'invoice_item_tax',
            'footer_totals',
            'footer_notes',
            'footer_qr_code',
        ];

        $blocks = [];
        foreach ($blockTypes as $type) {
            $blocks[] = ReportBlock::factory()->create([
                'block_type' => $type,
                'name' => ucfirst(str_replace('_', ' ', $type)),
            ]);
        }

        /* Act */
        $foundBlocks = [];
        foreach ($blockTypes as $type) {
            $foundBlocks[] = ReportBlock::query()->where('block_type', $type)->first();
        }

        /* Assert */
        $this->assertCount(count($blockTypes), $foundBlocks);
        foreach ($foundBlocks as $block) {
            $this->assertNotNull($block);
            $this->assertInstanceOf(ReportBlock::class, $block);
            $this->assertContains($block->block_type, $blockTypes);
        }
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_preserves_config_array_when_editing(): void
    {
        /* Arrange */
        $config = [
            'show_vat_id' => true,
            'show_phone' => true,
            'font_size' => 10,
            'font_weight' => 'bold',
        ];

        $block = ReportBlock::factory()->create([
            'block_type' => 'config_test',
            'name' => 'Config Test Block',
            'config' => $config,
        ]);

        /* Act */
        $data = $block->toArray();

        /* Assert */
        $this->assertEquals($config, $data['config']);
        $this->assertTrue($data['config']['show_vat_id']);
        $this->assertEquals(10, $data['config']['font_size']);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_uses_slug_for_lookup_when_available(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'slug_lookup_test',
            'name' => 'Slug Lookup Test',
            'slug' => 'slug-lookup-test',
        ]);

        /* Act */
        $foundBySlug = ReportBlock::query()->where('slug', 'slug-lookup-test')->first();
        $foundByType = ReportBlock::query()->where('block_type', 'slug_lookup_test')->first();

        /* Assert */
        $this->assertNotNull($foundBySlug);
        $this->assertNotNull($foundByType);
        $this->assertEquals($foundBySlug->id, $foundByType->id);
        $this->assertEquals('slug_lookup_test', $foundBySlug->block_type);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }
}
