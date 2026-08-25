<?php

namespace Modules\Core\Tests\Unit;

use Awcodes\Mason\Support\IframeRenderer;
use Modules\Core\Mason\ReportBricksCollection;
use Modules\Core\Mason\ReportIframeRenderer;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportIframeRendererTest extends AbstractTestCase
{
    #[Test]
    public function it_resolves_the_report_renderer_out_of_the_container(): void
    {
        /* Act */
        $renderer = IframeRenderer::make([]);

        /* Assert */
        $this->assertInstanceOf(ReportIframeRenderer::class, $renderer);
    }

    #[Test]
    public function it_paints_canvas_blocks_with_the_builder_preview_not_the_print_rendering(): void
    {
        /* Arrange */
        $renderer = $this->rendererFor([
            ['type' => 'masonBrick', 'attrs' => ['id' => 'header_company', 'config' => ['_width' => 'half']]],
        ]);

        /* Act */
        $html = $renderer->toHtml('mason::iframe-preview');

        /* Assert */
        $this->assertStringContainsString('background-color: #CCCCCC', $html);
        $this->assertStringNotContainsString('class="company-header"', $html);
    }

    #[Test]
    public function it_renders_the_configured_brick_into_the_canvas_document(): void
    {
        /* Arrange */
        $block    = ['type' => 'masonBrick', 'attrs' => ['id' => 'spacer', 'config' => ['height' => 42]]];
        $renderer = $this->rendererFor([$block]);

        /* Act */
        $blockHtml = (string) $renderer->getBlockHtml($block);
        $html      = $renderer->toHtml('mason::iframe-preview');

        /* Assert */
        $this->assertStringContainsString('42px', $blockHtml);
        $this->assertStringContainsString('42px', $html);
        $this->assertStringContainsString('data-brick-id="spacer"', $html);
    }

    #[Test]
    public function it_keeps_the_width_hint_available_to_the_preview_templates(): void
    {
        /* Arrange */
        $renderer = $this->rendererFor([
            ['type' => 'masonBrick', 'attrs' => ['id' => 'header_company', 'config' => ['_width' => 'half']]],
        ]);

        /* Act */
        $html = $renderer->toHtml('mason::iframe-preview');

        /* Assert */
        $this->assertStringContainsString('50%', $html);
    }

    protected function rendererFor(array $blocks): ReportIframeRenderer
    {
        $renderer = new ReportIframeRenderer($blocks);
        $renderer->bricks(ReportBricksCollection::all());

        return $renderer;
    }
}
