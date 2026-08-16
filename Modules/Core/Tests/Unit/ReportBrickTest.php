<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Enums\ReportBand;
use Modules\Core\Mason\Bricks\DetailItemsBrick;
use Modules\Core\Mason\Bricks\FooterTotalsBrick;
use Modules\Core\Mason\Bricks\HeaderCompanyBrick;
use Modules\Core\Mason\Bricks\PageBreakBrick;
use Modules\Core\Mason\Bricks\SpacerBrick;
use Modules\Core\Mason\ReportBricksCollection;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportBrickTest extends AbstractTestCase
{
    #[Test]
    public function it_infers_allowed_bands_from_the_class_name_prefix(): void
    {
        /* Assert */
        $this->assertSame([ReportBand::HEADER, ReportBand::GROUP_HEADER], HeaderCompanyBrick::allowedBands());
        $this->assertSame([ReportBand::DETAILS], DetailItemsBrick::allowedBands());
        $this->assertSame([ReportBand::GROUP_FOOTER, ReportBand::FOOTER], FooterTotalsBrick::allowedBands());
    }

    #[Test]
    public function it_derives_config_keys_from_the_configure_action_schema(): void
    {
        /* Act */
        $keys = HeaderCompanyBrick::configKeys();

        /* Assert */
        $this->assertContains('show_vat_id', $keys);
        $this->assertContains('font_size', $keys);
        $this->assertNotContains('unknown_key', $keys);
    }

    #[Test]
    public function it_reports_config_keys_for_every_registered_brick_without_error(): void
    {
        /* Assert */
        foreach (ReportBricksCollection::all() as $brick) {
            $this->assertIsArray($brick::configKeys(), "configKeys failed for {$brick}");
        }
    }

    #[Test]
    public function it_filters_config_to_declared_keys_only(): void
    {
        /* Act */
        $filtered = HeaderCompanyBrick::filterConfig([
            'show_vat_id' => true,
            'injected'    => 'payload',
        ]);

        /* Assert */
        $this->assertSame(['show_vat_id' => true], $filtered);
    }

    #[Test]
    public function it_has_no_config_keys_for_the_page_break_brick(): void
    {
        /* Assert */
        $this->assertSame([], PageBreakBrick::configKeys());
    }

    #[Test]
    public function it_renders_every_registered_brick_preview_and_html_without_error(): void
    {
        /* Assert */
        foreach (ReportBricksCollection::all() as $brick) {
            $this->assertIsString($brick::toPreviewHtml([]), "toPreviewHtml failed for {$brick}");
            $this->assertIsString($brick::toHtml([], []), "toHtml failed for {$brick}");
        }
    }

    #[Test]
    public function it_renders_a_page_break_as_page_break_css(): void
    {
        /* Act */
        $html = PageBreakBrick::toHtml([], []);

        /* Assert */
        $this->assertStringContainsString('page-break-after: always', $html);
    }

    #[Test]
    public function it_renders_the_spacer_with_the_configured_height(): void
    {
        /* Act */
        $html = SpacerBrick::toHtml(['height' => 42], []);

        /* Assert */
        $this->assertStringContainsString('height: 42px', $html);
    }
}
