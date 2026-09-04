<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\ReportBuilder\Bricks\DetailCustomerAgingBrick;
use Modules\Core\ReportBuilder\Bricks\DetailExpenseBrick;
use Modules\Core\ReportBuilder\Bricks\DetailInvoiceProductBrick;
use Modules\Core\ReportBuilder\Bricks\DetailItemsBrick;
use Modules\Core\ReportBuilder\Bricks\DetailQuoteProductBrick;
use Modules\Core\ReportBuilder\Bricks\FooterNotesBrick;
use Modules\Core\ReportBuilder\Bricks\FooterSummaryBrick;
use Modules\Core\ReportBuilder\Bricks\FooterTermsBrick;
use Modules\Core\ReportBuilder\Bricks\FooterTotalsBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderClientBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderCompanyBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderInvoiceMetaBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderQuoteMetaBrick;
use Modules\Core\ReportBuilder\Bricks\PageBreakBrick;
use Modules\Core\ReportBuilder\Bricks\SpacerBrick;
use Modules\Core\ReportBuilder\ReportBricksCollection;
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
        $this->assertCount(15, $bricks);
    }

    #[Test]
    public function it_returns_header_bricks(): void
    {
        /* Act */
        $headerBricks = ReportBricksCollection::header();

        /* Assert */
        $this->assertIsArray($headerBricks);
        $this->assertCount(4, $headerBricks);
        $this->assertContains(HeaderCompanyBrick::class, $headerBricks);
        $this->assertContains(HeaderClientBrick::class, $headerBricks);
        $this->assertContains(HeaderInvoiceMetaBrick::class, $headerBricks);
        $this->assertContains(HeaderQuoteMetaBrick::class, $headerBricks);
    }

    #[Test]
    public function it_returns_detail_bricks(): void
    {
        /* Act */
        $detailBricks = ReportBricksCollection::detail();

        /* Assert */
        $this->assertIsArray($detailBricks);
        $this->assertCount(5, $detailBricks);
        $this->assertContains(DetailItemsBrick::class, $detailBricks);
        $this->assertContains(DetailInvoiceProductBrick::class, $detailBricks);
        $this->assertContains(DetailQuoteProductBrick::class, $detailBricks);
        $this->assertContains(DetailCustomerAgingBrick::class, $detailBricks);
        $this->assertContains(DetailExpenseBrick::class, $detailBricks);
    }

    /**
     * HeaderProjectBrick, DetailTasksBrick, DetailInvoiceProjectBrick and
     * DetailQuoteProjectBrick are deliberately not registered — see the
     * comment on ReportBricksCollection::detail(). Invoices/quotes have no
     * FK to a Project/Task, so ReportDataMapper has nothing to feed them;
     * offering them would mean dragging a brick onto a real PDF that always
     * renders blank.
     */
    #[Test]
    public function it_does_not_offer_bricks_with_no_defined_data_source(): void
    {
        /* Act */
        $allBricks = ReportBricksCollection::all();

        /* Assert */
        $this->assertNotContains(\Modules\Core\ReportBuilder\Bricks\HeaderProjectBrick::class, $allBricks);
        $this->assertNotContains(\Modules\Core\ReportBuilder\Bricks\DetailTasksBrick::class, $allBricks);
        $this->assertNotContains(\Modules\Core\ReportBuilder\Bricks\DetailInvoiceProjectBrick::class, $allBricks);
        $this->assertNotContains(\Modules\Core\ReportBuilder\Bricks\DetailQuoteProjectBrick::class, $allBricks);
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
    public function it_excludes_quote_only_bricks_from_an_invoice_band(): void
    {
        /* Act */
        $headerBricks = ReportBricksCollection::forBand(ReportBand::HEADER, ReportTemplateType::INVOICE);
        $detailBricks = ReportBricksCollection::forBand(ReportBand::DETAILS, ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertContains(HeaderInvoiceMetaBrick::class, $headerBricks);
        $this->assertNotContains(HeaderQuoteMetaBrick::class, $headerBricks);

        $this->assertContains(DetailInvoiceProductBrick::class, $detailBricks);
        $this->assertNotContains(DetailQuoteProductBrick::class, $detailBricks);

        /* Untyped bricks stay available on every type */
        $this->assertContains(HeaderCompanyBrick::class, $headerBricks);
    }

    #[Test]
    public function it_excludes_invoice_only_bricks_from_a_quote_band(): void
    {
        /* Act */
        $headerBricks = ReportBricksCollection::forBand(ReportBand::HEADER, ReportTemplateType::QUOTE);
        $detailBricks = ReportBricksCollection::forBand(ReportBand::DETAILS, ReportTemplateType::QUOTE);

        /* Assert */
        $this->assertContains(HeaderQuoteMetaBrick::class, $headerBricks);
        $this->assertNotContains(HeaderInvoiceMetaBrick::class, $headerBricks);

        $this->assertContains(DetailQuoteProductBrick::class, $detailBricks);
        $this->assertNotContains(DetailInvoiceProductBrick::class, $detailBricks);
        $this->assertNotContains(DetailCustomerAgingBrick::class, $detailBricks);
        $this->assertNotContains(DetailExpenseBrick::class, $detailBricks);
    }

    #[Test]
    public function it_returns_every_type_when_no_type_filter_is_given(): void
    {
        /* Act */
        $headerBricks = ReportBricksCollection::forBand(ReportBand::HEADER);

        /* Assert — unfiltered call keeps existing (pre-type-filter) behavior */
        $this->assertContains(HeaderInvoiceMetaBrick::class, $headerBricks);
        $this->assertContains(HeaderQuoteMetaBrick::class, $headerBricks);
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
