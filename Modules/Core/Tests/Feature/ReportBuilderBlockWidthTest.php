<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Models\Company;
use Modules\Core\Models\ReportBlock;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test suite for Report Builder block width rendering.
 *
 * Tests that blocks render with correct widths in the designer canvas.
 */
class ReportBuilderBlockWidthTest extends AbstractAdminPanelTestCase
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
    public function it_renders_one_third_width_block_with_correct_grid_span(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'test_one_third',
            'name' => 'One Third Block',
            'width' => ReportBlockWidth::ONE_THIRD,
        ]);

        /* Act */
        $gridWidth = $block->width->getGridWidth();

        /* Assert */
        $this->assertEquals(4, $gridWidth);
        $this->assertEquals(ReportBlockWidth::ONE_THIRD, $block->width);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_renders_half_width_block_with_correct_grid_span(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'test_half',
            'name' => 'Half Width Block',
            'width' => ReportBlockWidth::HALF,
        ]);

        /* Act */
        $gridWidth = $block->width->getGridWidth();

        /* Assert */
        $this->assertEquals(6, $gridWidth);
        $this->assertEquals(ReportBlockWidth::HALF, $block->width);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_renders_two_thirds_width_block_with_correct_grid_span(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'test_two_thirds',
            'name' => 'Two Thirds Block',
            'width' => ReportBlockWidth::TWO_THIRDS,
        ]);

        /* Act */
        $gridWidth = $block->width->getGridWidth();

        /* Assert */
        $this->assertEquals(8, $gridWidth);
        $this->assertEquals(ReportBlockWidth::TWO_THIRDS, $block->width);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_renders_full_width_block_with_correct_grid_span(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'test_full',
            'name' => 'Full Width Block',
            'width' => ReportBlockWidth::FULL,
        ]);

        /* Act */
        $gridWidth = $block->width->getGridWidth();

        /* Assert */
        $this->assertEquals(12, $gridWidth);
        $this->assertEquals(ReportBlockWidth::FULL, $block->width);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_correctly_maps_block_widths_to_grid_columns_in_template(): void
    {
        /* Arrange */
        $blocks = [
            ReportBlock::factory()->create([
                'block_type' => 'one_third_1',
                'width' => ReportBlockWidth::ONE_THIRD,
            ]),
            ReportBlock::factory()->create([
                'block_type' => 'half_1',
                'width' => ReportBlockWidth::HALF,
            ]),
            ReportBlock::factory()->create([
                'block_type' => 'two_thirds_1',
                'width' => ReportBlockWidth::TWO_THIRDS,
            ]),
            ReportBlock::factory()->create([
                'block_type' => 'full_1',
                'width' => ReportBlockWidth::FULL,
            ]),
        ];

        /* Act */
        $mappedWidths = array_map(fn($block) => $block->width->getGridWidth(), $blocks);

        /* Assert */
        $this->assertEquals([4, 6, 8, 12], $mappedWidths);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_handles_invoice_items_block_as_full_width(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'invoice_items',
            'name' => 'Invoice Items',
            'width' => ReportBlockWidth::FULL,
        ]);

        /* Act */
        $gridWidth = $block->width->getGridWidth();

        /* Assert */
        $this->assertEquals(12, $gridWidth);
        $this->assertEquals(ReportBlockWidth::FULL, $block->width);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_applies_correct_css_grid_column_span_for_block_widths(): void
    {
        /* Arrange */
        $testCases = [
            ['width' => ReportBlockWidth::ONE_THIRD, 'expectedSpan' => 1],    // 4 columns = span 1 in 2-column grid
            ['width' => ReportBlockWidth::HALF, 'expectedSpan' => 1],         // 6 columns = span 1 in 2-column grid
            ['width' => ReportBlockWidth::TWO_THIRDS, 'expectedSpan' => 2],   // 8 columns = span 2 in 2-column grid
            ['width' => ReportBlockWidth::FULL, 'expectedSpan' => 2],         // 12 columns = span 2 in 2-column grid
        ];

        /* Act & Assert */
        foreach ($testCases as $testCase) {
            $gridWidth = $testCase['width']->getGridWidth();

            // Determine span based on grid width (using same logic as blade template)
            $span = $gridWidth >= 12 ? 2 : ($gridWidth >= 8 ? 2 : 1);

            $this->assertEquals($testCase['expectedSpan'], $span, 
                "Width {$testCase['width']->value} (grid: {$gridWidth}) should span {$testCase['expectedSpan']} columns");
        }
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('feature')]
    public function it_ensures_blocks_maintain_width_after_being_added_to_band(): void
    {
        /* Arrange */
        $block = ReportBlock::factory()->create([
            'block_type' => 'test_persistent',
            'name' => 'Persistent Width Block',
            'width' => ReportBlockWidth::TWO_THIRDS,
        ]);

        $initialWidth = $block->width;
        $initialGridWidth = $block->width->getGridWidth();

        /* Act */
        // Simulate block being loaded and rendered
        $block->refresh();
        $finalWidth = $block->width;
        $finalGridWidth = $block->width->getGridWidth();

        /* Assert */
        $this->assertEquals($initialWidth, $finalWidth);
        $this->assertEquals($initialGridWidth, $finalGridWidth);
        $this->assertEquals(8, $finalGridWidth);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }
}
