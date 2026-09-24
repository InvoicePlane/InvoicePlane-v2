<?php

namespace Modules\Core\Tests\Unit;

use Mockery;
use Modules\Core\Services\ReportRenderer;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class ReportRendererTest extends AbstractTestCase
{
    protected ReportRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new ReportRenderer();
    }

    /**
     * RB-08 (#761) — every branch of the group-subtotal fallback chain, not
     * just the {price,tax,total} shape the grouped render tests happen to use.
     *
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function groupSubtotalShapeProvider(): array
    {
        return [
            'explicit subtotal'           => [['subtotal' => 40, 'total' => 44, 'tax' => 4], '40.00'],
            'unit_price times quantity'   => [['unit_price' => 10, 'quantity' => 3], '30.00'],
            'unit_price without quantity' => [['unit_price' => 10], '10.00'],
            'price times quantity'        => [['price' => 7, 'quantity' => 4], '28.00'],
            'amount only'                 => [['amount' => 15], '15.00'],
            'total minus tax fallback'    => [['total' => 20, 'tax' => 5], '15.00'],
        ];
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

    #[Test]
    public function it_renders_grouped_bands_repeating_per_distinct_group_value_in_first_seen_order(): void
    {
        /* Arrange */
        $template = $this->template([
            'header'       => [['brick' => 'header_company', 'width' => 'full', 'config' => []]],
            'group_header' => [['brick' => 'detail_column_labels', 'width' => 'full', 'config' => []]],
            'details'      => [['brick' => 'detail_items', 'width' => 'full', 'config' => []]],
            'group_footer' => [['brick' => 'footer_totals', 'width' => 'full', 'config' => []]],
            'footer'       => [['brick' => 'footer_notes', 'width' => 'full', 'config' => ['footer_content' => '<p>Doc Footer</p>']]],
        ], [
            'band_options' => [
                'details' => ['group_by' => 'category'],
            ],
        ]);

        $data          = $this->data();
        $data['items'] = [
            ['description' => 'Consulting 1', 'category' => 'Services', 'quantity' => 1, 'price' => '100.00', 'tax' => '20.00', 'total' => '120.00'],
            ['description' => 'Laptop', 'category' => 'Hardware', 'quantity' => 2, 'price' => '500.00', 'tax' => '100.00', 'total' => '1100.00'],
            ['description' => 'Consulting 2', 'category' => 'Services', 'quantity' => 3, 'price' => '100.00', 'tax' => '30.00', 'total' => '330.00'],
        ];

        /* Act */
        $html = $this->renderer->render($template, $data);

        /* Assert */
        $this->assertSame(2, mb_substr_count($html, 'class="report-group"'), 'Expected exactly 2 group repetitions.');
        $this->assertSame(2, mb_substr_count($html, 'report-band-group_header'));
        $this->assertSame(2, mb_substr_count($html, 'report-band-details'));
        $this->assertSame(2, mb_substr_count($html, 'report-band-group_footer'));
        $this->assertSame(1, mb_substr_count($html, 'report-band-header'));
        $this->assertSame(1, mb_substr_count($html, 'report-band-footer'));

        // First group (Services) comes before second group (Hardware)
        $servicesPos = mb_strpos($html, 'Consulting 1');
        $hardwarePos = mb_strpos($html, 'Laptop');
        $this->assertNotFalse($servicesPos);
        $this->assertNotFalse($hardwarePos);
        $this->assertLessThan($hardwarePos, $servicesPos, 'Group order must match first-seen item order (Services before Hardware).');

        // Per-group totals
        // Services group: subtotal = 100 + 300 = 400.00, tax = 20 + 30 = 50.00, total = 120 + 330 = 450.00
        $this->assertStringContainsString('400.00', $html);
        $this->assertStringContainsString('450.00', $html);

        // Hardware group: subtotal = 1000.00, tax = 100.00, total = 1100.00
        $this->assertStringContainsString('1000.00', $html);
        $this->assertStringContainsString('1100.00', $html);
    }

    #[Test]
    public function it_preserves_first_seen_group_order_without_resorting(): void
    {
        /* Arrange */
        $template = $this->template([
            'group_header' => [['brick' => 'detail_column_labels', 'width' => 'full', 'config' => []]],
            'details'      => [['brick' => 'detail_items', 'width' => 'full', 'config' => []]],
        ], [
            'band_options' => [
                'details' => ['group_by' => 'category'],
            ],
        ]);

        $data          = $this->data();
        $data['items'] = [
            ['description' => 'Zeta Item', 'category' => 'Zeta', 'quantity' => 1, 'price' => '10.00', 'tax' => '0.00', 'total' => '10.00'],
            ['description' => 'Alpha Item', 'category' => 'Alpha', 'quantity' => 1, 'price' => '20.00', 'tax' => '0.00', 'total' => '20.00'],
            ['description' => 'Beta Item', 'category' => 'Beta', 'quantity' => 1, 'price' => '30.00', 'tax' => '0.00', 'total' => '30.00'],
        ];

        /* Act */
        $html = $this->renderer->render($template, $data);

        /* Assert */
        $zetaPos  = mb_strpos($html, 'Zeta Item');
        $alphaPos = mb_strpos($html, 'Alpha Item');
        $betaPos  = mb_strpos($html, 'Beta Item');

        $this->assertNotFalse($zetaPos);
        $this->assertNotFalse($alphaPos);
        $this->assertNotFalse($betaPos);
        $this->assertLessThan($alphaPos, $zetaPos, 'Zeta must come before Alpha in first-seen order.');
        $this->assertLessThan($betaPos, $alphaPos, 'Alpha must come before Beta in first-seen order.');
    }

    #[Test]
    public function it_applies_keep_together_to_grouped_repetitions(): void
    {
        /* Arrange */
        $template = $this->template([
            'details' => [['brick' => 'detail_items', 'width' => 'full', 'config' => []]],
        ], [
            'band_options' => [
                'details' => ['group_by' => 'category', 'keep_together' => true],
            ],
        ]);

        $data          = $this->data();
        $data['items'] = [
            ['description' => 'Item 1', 'category' => 'A', 'quantity' => 1, 'price' => '10.00', 'tax' => '0.00', 'total' => '10.00'],
        ];

        /* Act */
        $html = $this->renderer->render($template, $data);

        /* Assert */
        $this->assertStringContainsString('class="report-group" style="page-break-inside: avoid;"', $html);
    }

    #[Test]
    public function it_handles_empty_items_cleanly_when_group_by_is_configured(): void
    {
        /* Arrange */
        $template = $this->template([
            'header'       => [['brick' => 'header_company', 'width' => 'full', 'config' => []]],
            'group_header' => [['brick' => 'detail_column_labels', 'width' => 'full', 'config' => []]],
            'details'      => [['brick' => 'detail_items', 'width' => 'full', 'config' => []]],
            'footer'       => [['brick' => 'footer_totals', 'width' => 'full', 'config' => []]],
        ], [
            'band_options' => [
                'details' => ['group_by' => 'category'],
            ],
        ]);

        $data          = $this->data();
        $data['items'] = [];

        /* Act */
        $html = $this->renderer->render($template, $data);

        /* Assert */
        $this->assertStringContainsString('report-band-header', $html);
        $this->assertStringContainsString('report-band-footer', $html);
        $this->assertSame(1, mb_substr_count($html, 'class="report-group"'));
        $this->assertSame(1, mb_substr_count($html, 'report-band-details'));
    }

    #[Test]
    public function it_continues_rendering_when_a_brick_throws_an_exception(): void
    {
        /* Arrange */
        \Illuminate\Support\Facades\Log::shouldReceive('warning')
            ->once()
            ->with(Mockery::pattern('/Report brick throwing_brick failed: Boom/'), Mockery::any());

        $throwingBrick = new class () {
            public static function getId(): string
            {
                return 'throwing_brick';
            }

            public static function toHtml(array $config, array $data): string
            {
                throw new RuntimeException('Boom');
            }
        };

        $renderer = new class () extends ReportRenderer {
            public function callRenderBrickSafely(string $brickClass, array $config, array $data): string
            {
                return $this->renderBrickSafely($brickClass, $config, $data);
            }
        };

        /* Act */
        $output = $renderer->callRenderBrickSafely(get_class($throwingBrick), [], []);

        /* Assert */
        $this->assertSame('<!-- report brick throwing_brick failed to render -->', $output);
    }

    #[Test]
    public function it_renders_grouped_details_band_once_for_zero_item_documents(): void
    {
        /* Arrange */
        $template = $this->template([
            'header'       => [['brick' => 'header_company', 'width' => 'full', 'config' => []]],
            'group_header' => [['brick' => 'detail_column_labels', 'width' => 'full', 'config' => []]],
            'details'      => [['brick' => 'detail_items', 'width' => 'full', 'config' => []]],
            'group_footer' => [['brick' => 'footer_totals', 'width' => 'full', 'config' => []]],
            'footer'       => [['brick' => 'footer_notes', 'width' => 'full', 'config' => []]],
        ], [
            'band_options' => [
                'details' => ['group_by' => 'category'],
            ],
        ]);

        $data          = $this->data();
        $data['items'] = [];

        /* Act */
        $html = $this->renderer->render($template, $data);

        /* Assert */
        $this->assertSame(1, mb_substr_count($html, 'class="report-group"'));
        $this->assertSame(1, mb_substr_count($html, 'report-band-details'));
        $this->assertStringContainsString('report-band-header', $html);
        $this->assertStringContainsString('report-band-footer', $html);
    }

    #[Test]
    public function preview_uses_the_same_row_grid_as_print_and_omits_the_document_wrapper(): void
    {
        /* Arrange */
        $bands = [
            'header' => [
                ['brick' => 'header_company', 'width' => 'half', 'config' => []],
                ['brick' => 'header_client', 'width' => 'half', 'config' => []],
            ],
            'group_header' => [],
            'details'      => [],
            'group_footer' => [],
            'footer'       => [],
        ];

        /* Act */
        $preview = $this->renderer->renderPreview($bands);
        $print   = $this->renderer->render($this->template(['header' => $bands['header']]), $this->data());

        /* Assert — preview shares renderRow()/chunkIntoRows() with print … */
        $this->assertStringContainsString('class="report-row"', $preview);
        $this->assertSame(2, mb_substr_count($preview, 'class="report-block"'));
        $this->assertStringContainsString('width: 50%', $preview);
        $this->assertSame(
            mb_substr_count($print, 'class="report-block"'),
            mb_substr_count($preview, 'class="report-block"'),
            'preview and print must pack the same bricks into the same number of grid cells',
        );

        /* … but has no <html>/<style> document shell */
        $this->assertStringNotContainsString('<!DOCTYPE html>', $preview);
        $this->assertStringNotContainsString('<style>', $preview);
    }

    #[Test]
    public function preview_renders_each_brick_via_to_preview_html_not_to_html(): void
    {
        /* Arrange */
        $config = ['footer_content' => '<p>PREVIEW BODY</p>'];
        $bands  = ['footer' => [['brick' => 'footer_notes', 'width' => 'full', 'config' => $config]]];

        /* Act */
        $preview = $this->renderer->renderPreview($bands + [
            'header' => [], 'group_header' => [], 'details' => [], 'group_footer' => [],
        ]);

        /* Assert */
        $this->assertStringContainsString(
            mb_trim((string) \Modules\Core\ReportBuilder\Bricks\FooterNotesBrick::toPreviewHtml($config)),
            $preview,
        );
    }

    #[Test]
    public function preview_isolates_a_throwing_brick_like_print_does(): void
    {
        /* Arrange */
        \Illuminate\Support\Facades\Log::shouldReceive('warning')->atLeast()->once();

        $renderer = new class () extends ReportRenderer {
            public function callPreviewBrick(string $brickClass, array $config): string
            {
                return $this->renderBrickSafely($brickClass, $config, [], preview: true);
            }
        };

        $throwing = new class () {
            public static function getId(): string
            {
                return 'boom_brick';
            }

            public static function toPreviewHtml(array $config): string
            {
                throw new RuntimeException('kaboom');
            }
        };

        /* Act */
        $out = $renderer->callPreviewBrick($throwing::class, []);

        /* Assert */
        $this->assertSame('<!-- report brick boom_brick failed to render -->', $out);
    }

    #[Test]
    #[DataProvider('groupSubtotalShapeProvider')]
    public function it_computes_group_subtotals_for_each_item_shape(array $item, string $expectedSubtotal): void
    {
        /* Arrange */
        $renderer = new class () extends ReportRenderer {
            public function callGroupTotals(array $items): array
            {
                return $this->calculateGroupTotals($items);
            }
        };

        /* Act */
        $totals = $renderer->callGroupTotals([$item]);

        /* Assert */
        $this->assertSame($expectedSubtotal, $totals['subtotal']);
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
