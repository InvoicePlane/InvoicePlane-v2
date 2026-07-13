<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Enums\ReportBand;
use Modules\Core\Mason\Bricks\DetailCustomerAgingBrick;
use Modules\Core\Mason\Bricks\DetailExpenseBrick;
use Modules\Core\Mason\Bricks\DetailInvoiceProductBrick;
use Modules\Core\Mason\Bricks\DetailInvoiceProjectBrick;
use Modules\Core\Mason\Bricks\DetailItemsBrick;
use Modules\Core\Mason\Bricks\DetailQuoteProductBrick;
use Modules\Core\Mason\Bricks\DetailQuoteProjectBrick;
use Modules\Core\Mason\Bricks\DetailTasksBrick;
use Modules\Core\Mason\Bricks\FooterNotesBrick;
use Modules\Core\Mason\Bricks\FooterSummaryBrick;
use Modules\Core\Mason\Bricks\FooterTermsBrick;
use Modules\Core\Mason\Bricks\FooterTotalsBrick;
use Modules\Core\Mason\Bricks\HeaderClientBrick;
use Modules\Core\Mason\Bricks\HeaderCompanyBrick;
use Modules\Core\Mason\Bricks\HeaderInvoiceMetaBrick;
use Modules\Core\Mason\Bricks\HeaderProjectBrick;
use Modules\Core\Mason\Bricks\HeaderQuoteMetaBrick;
use Modules\Core\Mason\Bricks\PageBreakBrick;
use Modules\Core\Mason\Bricks\SpacerBrick;
use Modules\Core\Mason\ReportBricksCollection;
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
        $this->assertCount(19, $bricks);
    }

    #[Test]
    public function it_returns_header_bricks(): void
    {
        /* Act */
        $headerBricks = ReportBricksCollection::header();

        /* Assert */
        $this->assertIsArray($headerBricks);
        $this->assertCount(5, $headerBricks);
        $this->assertContains(HeaderCompanyBrick::class, $headerBricks);
        $this->assertContains(HeaderClientBrick::class, $headerBricks);
        $this->assertContains(HeaderInvoiceMetaBrick::class, $headerBricks);
        $this->assertContains(HeaderQuoteMetaBrick::class, $headerBricks);
        $this->assertContains(HeaderProjectBrick::class, $headerBricks);
    }

    #[Test]
    public function it_returns_detail_bricks(): void
    {
        /* Act */
        $detailBricks = ReportBricksCollection::detail();

        /* Assert */
        $this->assertIsArray($detailBricks);
        $this->assertCount(8, $detailBricks);
        $this->assertContains(DetailItemsBrick::class, $detailBricks);
        $this->assertContains(DetailTasksBrick::class, $detailBricks);
        $this->assertContains(DetailInvoiceProductBrick::class, $detailBricks);
        $this->assertContains(DetailInvoiceProjectBrick::class, $detailBricks);
        $this->assertContains(DetailQuoteProductBrick::class, $detailBricks);
        $this->assertContains(DetailQuoteProjectBrick::class, $detailBricks);
        $this->assertContains(DetailCustomerAgingBrick::class, $detailBricks);
        $this->assertContains(DetailExpenseBrick::class, $detailBricks);
    }

    #[Test]
    public function it_returns_footer_bricks(): void
    {
        /* Act */
        $footerBricks = ReportBricksCollection::footer();

        /* Assert */
        $this->assertIsArray($footerBricks);
        $this->assertCount(4, $footerBricks);
        $this->assertContains(FooterTotalsBrick::class, $footerBricks);
        $this->assertContains(FooterNotesBrick::class, $footerBricks);
        $this->assertContains(FooterTermsBrick::class, $footerBricks);
        $this->assertContains(FooterSummaryBrick::class, $footerBricks);
    }

    #[Test]
    public function it_all_method_combines_all_sections(): void
    {
        /* Arrange */
        $headerCount  = count(ReportBricksCollection::header());
        $detailCount  = count(ReportBricksCollection::detail());
        $footerCount  = count(ReportBricksCollection::footer());
        $utilityCount = count(ReportBricksCollection::utility());

        /* Act */
        $allBricks = ReportBricksCollection::all();

        /* Assert */
        $this->assertCount($headerCount + $detailCount + $footerCount + $utilityCount, $allBricks);
    }

    #[Test]
    public function it_returns_only_bricks_allowed_in_the_requested_band(): void
    {
        /* Act */
        $headerBricks = ReportBricksCollection::forBand(ReportBand::HEADER);
        $detailBricks = ReportBricksCollection::forBand(ReportBand::DETAILS);

        /* Assert */
        $this->assertContains(HeaderCompanyBrick::class, $headerBricks);
        $this->assertNotContains(DetailItemsBrick::class, $headerBricks);
        $this->assertNotContains(FooterTotalsBrick::class, $headerBricks);

        $this->assertContains(DetailItemsBrick::class, $detailBricks);
        $this->assertNotContains(HeaderCompanyBrick::class, $detailBricks);
    }

    #[Test]
    public function it_allows_utility_bricks_in_every_band(): void
    {
        /* Assert */
        foreach (ReportBand::cases() as $band) {
            $bandBricks = ReportBricksCollection::forBand($band);

            $this->assertContains(PageBreakBrick::class, $bandBricks, "Page break should be allowed in {$band->value}");
            $this->assertContains(SpacerBrick::class, $bandBricks, "Spacer should be allowed in {$band->value}");
        }
    }

    #[Test]
    public function it_finds_a_brick_class_by_its_id(): void
    {
        /* Assert */
        $this->assertSame(HeaderCompanyBrick::class, ReportBricksCollection::findById('header_company'));
        $this->assertSame(PageBreakBrick::class, ReportBricksCollection::findById('page_break'));
        $this->assertNull(ReportBricksCollection::findById('does_not_exist'));
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
