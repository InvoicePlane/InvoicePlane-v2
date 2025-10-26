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
        $handler = BlockFactory::make('header_company');

        $this->assertInstanceOf(HeaderCompanyBlockHandler::class, $handler);
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_detail_items_handler(): void
    {
        $handler = BlockFactory::make('detail_items');

        $this->assertInstanceOf(DetailItemsBlockHandler::class, $handler);
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_footer_notes_handler(): void
    {
        $handler = BlockFactory::make('footer_notes');

        $this->assertInstanceOf(FooterNotesBlockHandler::class, $handler);
    }

    #[Test]
    #[Group('unit')]
    public function it_throws_exception_for_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported block type: invalid_type');

        BlockFactory::make('invalid_type');
    }

    #[Test]
    #[Group('unit')]
    public function it_returns_all_block_types(): void
    {
        $blockTypes = BlockFactory::all();

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
}
