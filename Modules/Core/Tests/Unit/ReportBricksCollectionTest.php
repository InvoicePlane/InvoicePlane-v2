<?php

namespace Modules\Core\Tests\Unit;

use App\Mason\Bricks\DetailItemsBrick;
use App\Mason\Bricks\FooterNotesBrick;
use App\Mason\Bricks\FooterTotalsBrick;
use App\Mason\Bricks\HeaderClientBrick;
use App\Mason\Bricks\HeaderCompanyBrick;
use App\Mason\Bricks\HeaderInvoiceMetaBrick;
use App\Mason\Collections\ReportBricksCollection;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportBricksCollectionTest extends AbstractTestCase
{
    #[Test]
    public function it_returns_all_bricks(): void
    {
        /* Act */
        $bricks = ReportBricksCollection::all();

        /* Assert */
        $this->assertIsArray($bricks);
        $this->assertCount(6, $bricks);
    }

    #[Test]
    public function it_returns_header_bricks(): void
    {
        /* Act */
        $headerBricks = ReportBricksCollection::header();

        /* Assert */
        $this->assertIsArray($headerBricks);
        $this->assertCount(3, $headerBricks);
        $this->assertContains(HeaderCompanyBrick::class, $headerBricks);
        $this->assertContains(HeaderClientBrick::class, $headerBricks);
        $this->assertContains(HeaderInvoiceMetaBrick::class, $headerBricks);
    }

    #[Test]
    public function it_returns_detail_bricks(): void
    {
        /* Act */
        $detailBricks = ReportBricksCollection::detail();

        /* Assert */
        $this->assertIsArray($detailBricks);
        $this->assertCount(1, $detailBricks);
        $this->assertContains(DetailItemsBrick::class, $detailBricks);
    }

    #[Test]
    public function it_returns_footer_bricks(): void
    {
        /* Act */
        $footerBricks = ReportBricksCollection::footer();

        /* Assert */
        $this->assertIsArray($footerBricks);
        $this->assertCount(2, $footerBricks);
        $this->assertContains(FooterTotalsBrick::class, $footerBricks);
        $this->assertContains(FooterNotesBrick::class, $footerBricks);
    }

    #[Test]
    public function it_all_method_combines_all_sections(): void
    {
        /* Arrange */
        $headerCount = count(ReportBricksCollection::header());
        $detailCount = count(ReportBricksCollection::detail());
        $footerCount = count(ReportBricksCollection::footer());

        /* Act */
        $allBricks = ReportBricksCollection::all();

        /* Assert */
        $this->assertCount($headerCount + $detailCount + $footerCount, $allBricks);
    }

    #[Test]
    public function it_all_bricks_are_valid_class_names(): void
    {
        /* Act */
        $allBricks = ReportBricksCollection::all();

        /* Assert */
        foreach ($allBricks as $brick) {
            $this->assertTrue(class_exists($brick), "Class {$brick} should exist");
        }
    }

    #[Test]
    public function it_no_duplicate_bricks_in_collection(): void
    {
        /* Act */
        $allBricks = ReportBricksCollection::all();

        /* Assert */
        $uniqueBricks = array_unique($allBricks);
        $this->assertCount(count($allBricks), $uniqueBricks);
    }
}
