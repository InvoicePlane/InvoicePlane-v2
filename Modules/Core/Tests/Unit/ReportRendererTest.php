<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Services\ReportRenderer;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportRendererTest extends AbstractTestCase
{
    protected ReportRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new ReportRenderer();
    }

    #[Test]
    public function it_renders_configured_bricks_with_entity_data(): void
    {
        /* Act */
        $html = $this->renderer->render($this->template([
            'header'  => [['brick' => 'header_company', 'width' => 'half', 'config' => []]],
            'details' => [['brick' => 'detail_items', 'width' => 'full', 'config' => []]],
        ]), $this->data());

        /* Assert */
        $this->assertStringContainsString('ACME Corp', $html);
        $this->assertStringContainsString('Widget', $html);
        $this->assertStringContainsString('report-band-header', $html);
        $this->assertStringContainsString('report-band-details', $html);
    }

    #[Test]
    public function it_omits_fields_toggled_off_in_the_brick_config(): void
    {
        /* Act */
        $html = $this->renderer->render($this->template([
            'header' => [['brick' => 'header_company', 'width' => 'full', 'config' => ['show_vat_id' => false]]],
        ]), $this->data());

        /* Assert */
        $this->assertStringContainsString('ACME Corp', $html);
        $this->assertStringNotContainsString('VAT-123', $html);
    }

    #[Test]
    public function it_skips_bands_without_entries_and_unknown_bricks(): void
    {
        /* Act */
        $html = $this->renderer->render($this->template([
            'header' => [['brick' => 'nonexistent_brick', 'width' => 'full', 'config' => []]],
        ]), $this->data());

        /* Assert */
        $this->assertStringNotContainsString('report-band-details', $html);
        $this->assertStringNotContainsString('nonexistent', $html);
    }

    #[Test]
    public function it_marks_keep_together_bands_with_page_break_css(): void
    {
        /* Act */
        $html = $this->renderer->render($this->template(
            ['footer' => [['brick' => 'footer_totals', 'width' => 'full', 'config' => []]]],
            ['band_options' => ['footer' => ['keep_together' => true]]],
        ), $this->data());

        /* Assert */
        $this->assertStringContainsString('page-break-inside: avoid', $html);
    }

    #[Test]
    public function it_renders_manual_page_breaks_and_spacers(): void
    {
        /* Act */
        $html = $this->renderer->render($this->template([
            'details' => [
                ['brick' => 'page_break', 'width' => 'full', 'config' => []],
                ['brick' => 'spacer', 'width' => 'full', 'config' => ['height' => 55]],
            ],
        ]), $this->data());

        /* Assert */
        $this->assertStringContainsString('page-break-after: always', $html);
        $this->assertStringContainsString('height: 55px', $html);
    }

    #[Test]
    public function it_applies_block_widths_as_percentages(): void
    {
        /* Act */
        $html = $this->renderer->render($this->template([
            'header' => [['brick' => 'header_company', 'width' => 'half', 'config' => []]],
        ]), $this->data());

        /* Assert */
        $this->assertStringContainsString('width: 50%', $html);
    }

    protected function template(array $bands, array $manifest = []): array
    {
        return [
            'manifest' => array_merge(['name' => 'Test', 'slug' => 'test', 'type' => 'invoice'], $manifest),
            'bands'    => array_merge(
                ['header' => [], 'group_header' => [], 'details' => [], 'group_footer' => [], 'footer' => []],
                $bands,
            ),
        ];
    }

    protected function data(): array
    {
        return [
            'company' => ['name' => 'ACME Corp', 'vat_id' => 'VAT-123', 'address' => 'Main Street 1'],
            'client'  => ['name' => 'Client Co'],
            'invoice' => ['number' => 'INV-001', 'date' => '2026-01-01', 'due_date' => '2026-02-01'],
            'items'   => [['description' => 'Widget', 'quantity' => 2, 'price' => '10.00', 'tax' => '4.00', 'total' => '24.00']],
            'totals'  => ['subtotal' => '20.00', 'tax' => '4.00', 'total' => '24.00', 'paid' => '0.00', 'balance' => '24.00'],
        ];
    }
}
