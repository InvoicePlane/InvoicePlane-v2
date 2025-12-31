<?php

namespace Modules\ReportBuilder\Tests\Unit;

use InvalidArgumentException;
use Modules\ReportBuilder\Handlers\DetailItemsBlockHandler;
use Modules\ReportBuilder\Handlers\FooterNotesBlockHandler;
use Modules\ReportBuilder\Handlers\HeaderCompanyBlockHandler;
use Modules\ReportBuilder\Services\BlockFactory;
use Modules\ReportBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class BlockFactoryTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function it_creates_header_company_handler(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $handler = BlockFactory::make('header_company');

        /* assert */
        $this->assertInstanceOf(HeaderCompanyBlockHandler::class, $handler);
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_detail_items_handler(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $handler = BlockFactory::make('detail_items');

        /* assert */
        $this->assertInstanceOf(DetailItemsBlockHandler::class, $handler);
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_footer_notes_handler(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $handler = BlockFactory::make('footer_notes');

        /* assert */
        $this->assertInstanceOf(FooterNotesBlockHandler::class, $handler);
    }

    #[Test]
    #[Group('unit')]
    public function it_throws_exception_for_invalid_type(): void
    {
        /* arrange */
        // No setup needed

        /* assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Unsupported block type/i');
        BlockFactory::make('invalid_type');
    }

    #[Test]
    #[Group('unit')]
    public function it_returns_all_block_types(): void
    {
        /* arrange */
        // No setup needed

        /* act */
        $blockTypes = BlockFactory::all();

        /* assert */
        $this->assertIsArray($blockTypes);
        $this->assertNotEmpty($blockTypes);
        $this->assertCount(8, $blockTypes);
        foreach ($blockTypes as $block) {
            $this->assertArrayHasKey('type', $block);
            $this->assertArrayHasKey('label', $block);
            $this->assertArrayHasKey('category', $block);
            $this->assertArrayHasKey('description', $block);
            $this->assertArrayHasKey('icon', $block);
        }
    }

    #[Test]
    #[Group('unit')]
    public function all_returned_types_are_creatable(): void
    {
        $blockTypes = BlockFactory::all();

        foreach ($blockTypes as $block) {
            $handler = BlockFactory::make($block['type']);
            $this->assertNotNull($handler);
        }
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_block(): void
    {
        $this->markTestIncomplete('Test incomplete - requires investigation for PHPStan coverage and implementation details');
    }
}
