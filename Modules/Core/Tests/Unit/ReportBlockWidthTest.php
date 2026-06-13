<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test suite for ReportBlockWidth enum functionality.
 *
 * Tests the enhanced width options and grid width calculations.
 */
class ReportBlockWidthTest extends AbstractTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_has_one_third_width_option(): void
    {
        /* Arrange */
        $width = ReportBlockWidth::ONE_THIRD;

        /* Act */
        $gridWidth = $width->getGridWidth();

        /* Assert */
        $this->assertEquals('one_third', $width->value);
        $this->assertEquals(4, $gridWidth);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_has_half_width_option(): void
    {
        /* Arrange */
        $width = ReportBlockWidth::HALF;

        /* Act */
        $gridWidth = $width->getGridWidth();

        /* Assert */
        $this->assertEquals('half', $width->value);
        $this->assertEquals(6, $gridWidth);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_has_two_thirds_width_option(): void
    {
        /* Arrange */
        $width = ReportBlockWidth::TWO_THIRDS;

        /* Act */
        $gridWidth = $width->getGridWidth();

        /* Assert */
        $this->assertEquals('two_thirds', $width->value);
        $this->assertEquals(8, $gridWidth);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_has_full_width_option(): void
    {
        /* Arrange */
        $width = ReportBlockWidth::FULL;

        /* Act */
        $gridWidth = $width->getGridWidth();

        /* Assert */
        $this->assertEquals('full', $width->value);
        $this->assertEquals(12, $gridWidth);
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_supports_all_width_values(): void
    {
        /* Arrange */
        $expectedWidths = [
            'one_third'  => 4,
            'half'       => 6,
            'two_thirds' => 8,
            'full'       => 12,
        ];

        /* Act */
        $cases = ReportBlockWidth::cases();

        /* Assert */
        $this->assertCount(4, $cases);
        foreach ($cases as $case) {
            $this->assertArrayHasKey($case->value, $expectedWidths);
            $this->assertEquals($expectedWidths[$case->value], $case->getGridWidth());
        }
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }

    #[Test]
    #[Group('unit')]
    public function it_calculates_correct_grid_widths_for_12_column_grid(): void
    {
        /* Arrange */
        $widths = [
            ReportBlockWidth::ONE_THIRD->value  => 4,  // 1/3 of 12 = 4
            ReportBlockWidth::HALF->value       => 6,       // 1/2 of 12 = 6
            ReportBlockWidth::TWO_THIRDS->value => 8, // 2/3 of 12 = 8
            ReportBlockWidth::FULL->value       => 12,      // 12/12 = 12
        ];

        /* Act & Assert */
        foreach ($widths as $widthString => $expectedGrid) {
            $width = ReportBlockWidth::from($widthString);
            $actualGrid = $width->getGridWidth();
            $this->assertEquals($expectedGrid, $actualGrid, "Width {$width->value} should map to {$expectedGrid} grid columns");
        }
        $this->markTestIncomplete('Test implementation complete but marked incomplete as per requirements');
    }
}
