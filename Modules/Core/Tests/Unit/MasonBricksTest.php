<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Enums\FieldPlacement;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\ReportBuilder\Bricks\DetailColumnLabelsBrick;
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
use Modules\Core\ReportBuilder\ReportBricksCollection;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MasonBricksTest extends AbstractTestCase
{
    public static function widthProvider(): array
    {
        return [
            'one_third'  => [ReportBlockWidth::ONE_THIRD->value],
            'half'       => [ReportBlockWidth::HALF->value],
            'two_thirds' => [ReportBlockWidth::TWO_THIRDS->value],
            'full'       => [ReportBlockWidth::FULL->value],
        ];
    }

    #[Test]
    public function it_header_company_brick_has_correct_id(): void
    {
        /* Act */
        $id = HeaderCompanyBrick::getId();

        /* Assert */
        $this->assertEquals('header_company', $id);
    }

    #[Test]
    public function it_header_company_brick_generates_preview_html(): void
    {
        /* Arrange */
        $config = [
            'show_vat_id' => true,
            'show_phone'  => true,
            'font_size'   => 10,
        ];

        /* Act */
        $html = HeaderCompanyBrick::toPreviewHtml($config);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString(trans('ip.company_name'), $html);
    }

    #[Test]
    public function it_header_company_brick_generates_render_html(): void
    {
        /* Arrange */
        $config = ['show_vat_id' => true];
        $data   = [
            'company' => [
                'name'   => 'Test Company',
                'vat_id' => '123456',
            ],
        ];

        /* Act */
        $html = HeaderCompanyBrick::toHtml($config, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('Test Company', $html);
    }

    #[Test]
    public function it_header_client_brick_has_correct_id(): void
    {
        /* Act */
        $id = HeaderClientBrick::getId();

        /* Assert */
        $this->assertEquals('header_client', $id);
    }

    #[Test]
    public function it_header_client_brick_generates_html(): void
    {
        /* Arrange */
        $config = ['show_phone' => true];
        $data   = [
            'client' => [
                'name'  => 'Test Client',
                'phone' => '555-1234',
            ],
        ];

        /* Act */
        $html = HeaderClientBrick::toHtml($config, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('Test Client', $html);
    }

    #[Test]
    public function it_header_invoice_meta_brick_has_correct_id(): void
    {
        /* Act */
        $id = HeaderInvoiceMetaBrick::getId();

        /* Assert */
        $this->assertEquals('header_invoice_meta', $id);
    }

    #[Test]
    public function it_header_invoice_meta_brick_shows_configured_fields(): void
    {
        /* Arrange */
        $config = [
            'show_invoice_number' => true,
            'show_invoice_date'   => true,
            'show_due_date'       => false,
        ];
        $data = [
            'invoice' => [
                'number' => 'INV-001',
                'date'   => '2024-01-01',
            ],
        ];

        /* Act */
        $html = HeaderInvoiceMetaBrick::toHtml($config, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('INV-001', $html);
    }

    #[Test]
    public function it_detail_column_labels_brick_has_correct_id(): void
    {
        /* Act */
        $id = DetailColumnLabelsBrick::getId();

        /* Assert */
        $this->assertEquals('detail_column_labels', $id);
    }

    #[Test]
    public function it_detail_column_labels_brick_generates_preview_html(): void
    {
        /* Arrange */
        $config = ['show_description' => true, 'show_quantity' => true, 'show_price' => true];

        /* Act */
        $html = DetailColumnLabelsBrick::toPreviewHtml($config);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString(trans('ip.description'), $html);
        $this->assertStringContainsString(trans('ip.quantity'), $html);
        $this->assertStringContainsString(trans('ip.price'), $html);
    }

    #[Test]
    public function it_detail_column_labels_brick_generates_render_html(): void
    {
        /* Arrange */
        $config = ['show_description' => true, 'show_total' => true];
        $data   = [];

        /* Act */
        $html = DetailColumnLabelsBrick::toHtml($config, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString(trans('ip.description'), $html);
        $this->assertStringContainsString(trans('ip.total'), $html);
    }

    #[Test]
    public function it_detail_items_brick_has_correct_id(): void
    {
        /* Act */
        $id = DetailItemsBrick::getId();

        /* Assert */
        $this->assertEquals('detail_items', $id);
    }

    #[Test]
    public function it_detail_items_brick_renders_items_table(): void
    {
        /* Arrange */
        $config = [
            'show_description' => true,
            'show_quantity'    => true,
            'show_price'       => true,
        ];
        $data = [
            'items' => [
                [
                    'description' => 'Item 1',
                    'quantity'    => 2,
                    'price'       => '100.00',
                ],
            ],
        ];

        /* Act */
        $html = DetailItemsBrick::toHtml($config, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('Item 1', $html);
    }

    #[Test]
    public function it_footer_totals_brick_has_correct_id(): void
    {
        /* Act */
        $id = FooterTotalsBrick::getId();

        /* Assert */
        $this->assertEquals('footer_totals', $id);
    }

    #[Test]
    public function it_footer_totals_brick_displays_configured_totals(): void
    {
        /* Arrange */
        $config = [
            'show_subtotal' => true,
            'show_tax'      => true,
            'show_total'    => true,
        ];
        $data = [
            'totals' => [
                'subtotal' => '100.00',
                'tax'      => '10.00',
                'total'    => '110.00',
            ],
        ];

        /* Act */
        $html = FooterTotalsBrick::toHtml($config, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('110.00', $html);
    }

    #[Test]
    public function it_footer_notes_brick_has_correct_id(): void
    {
        /* Act */
        $id = FooterNotesBrick::getId();

        /* Assert */
        $this->assertEquals('footer_notes', $id);
    }

    #[Test]
    public function it_footer_notes_brick_renders_custom_content(): void
    {
        /* Arrange */
        $config = [
            'footer_content' => '<p>Custom payment terms</p>',
        ];
        $data = [];

        /* Act */
        $html = FooterNotesBrick::toHtml($config, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('Custom payment terms', $html);
    }

    #[Test]
    public function it_escapes_user_authored_data_fields_in_footer_bricks(): void
    {
        /* Arrange */
        $malicious = '<script>alert(1)</script><b>ok</b>';

        /* Act */
        $notesHtml   = FooterNotesBrick::toHtml([], ['footer' => $malicious]);
        $termsHtml   = FooterTermsBrick::toHtml([], ['terms' => $malicious]);
        $summaryHtml = FooterSummaryBrick::toHtml([], ['summary' => $malicious]);

        /* Assert */
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $notesHtml);
        $this->assertStringNotContainsString('<script>', $notesHtml);

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $termsHtml);
        $this->assertStringNotContainsString('<script>', $termsHtml);

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $summaryHtml);
        $this->assertStringNotContainsString('<script>', $summaryHtml);
    }

    #[Test]
    public function it_purifies_rich_content_config_in_footer_bricks_and_previews(): void
    {
        /* Arrange */
        $richContent = '<script>alert(1)</script><b>allowed bold</b><p>paragraph</p>';

        /* Act */
        $notesHtml      = FooterNotesBrick::toHtml(['footer_content' => $richContent], []);
        $notesPreview   = FooterNotesBrick::toPreviewHtml(['footer_content' => $richContent]);
        $termsHtml      = FooterTermsBrick::toHtml(['terms_content' => $richContent], []);
        $termsPreview   = FooterTermsBrick::toPreviewHtml(['terms_content' => $richContent]);
        $summaryHtml    = FooterSummaryBrick::toHtml(['summary_content' => $richContent], []);
        $summaryPreview = FooterSummaryBrick::toPreviewHtml(['summary_content' => $richContent]);

        /* Assert */
        foreach ([$notesHtml, $notesPreview, $termsHtml, $termsPreview, $summaryHtml, $summaryPreview] as $html) {
            $this->assertStringNotContainsString('<script>', $html);
            $this->assertStringNotContainsString('alert(1)', $html);
            $this->assertStringContainsString('<b>allowed bold</b>', $html);
        }
    }

    #[Test]
    public function it_footer_notes_brick_renders_rich_content_unescaped(): void
    {
        /* Arrange */
        $config = ['footer_content' => '<p>Custom <strong>bold</strong> terms</p>'];

        /* Act */
        $html = FooterNotesBrick::toHtml($config, []);

        /* Assert */
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringNotContainsString('&lt;strong&gt;', $html);
    }

    #[Test]
    public function it_footer_notes_preview_renders_rich_content_unescaped(): void
    {
        /* Arrange */
        $config = ['footer_content' => '<p>Custom <strong>bold</strong> terms</p>'];

        /* Act */
        $html = FooterNotesBrick::toPreviewHtml($config);

        /* Assert */
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringNotContainsString('&lt;strong&gt;', $html);
    }

    #[Test]
    public function it_footer_terms_brick_renders_rich_content_unescaped(): void
    {
        /* Arrange */
        $config = ['terms_content' => '<p>Payment due in <strong>30 days</strong></p>'];

        /* Act */
        $html = FooterTermsBrick::toHtml($config, []);

        /* Assert */
        $this->assertStringContainsString('<strong>30 days</strong>', $html);
        $this->assertStringNotContainsString('&lt;strong&gt;', $html);
    }

    #[Test]
    public function it_footer_terms_preview_renders_rich_content_unescaped(): void
    {
        /* Arrange */
        $config = ['terms_content' => '<p>Payment due in <strong>30 days</strong></p>'];

        /* Act */
        $html = FooterTermsBrick::toPreviewHtml($config);

        /* Assert */
        $this->assertStringContainsString('<strong>30 days</strong>', $html);
        $this->assertStringNotContainsString('&lt;strong&gt;', $html);
    }

    #[Test]
    public function it_footer_summary_brick_renders_rich_content_unescaped(): void
    {
        /* Arrange */
        $config = ['summary_content' => '<p>Thank you <strong>very much</strong></p>'];

        /* Act */
        $html = FooterSummaryBrick::toHtml($config, []);

        /* Assert */
        $this->assertStringContainsString('<strong>very much</strong>', $html);
        $this->assertStringNotContainsString('&lt;strong&gt;', $html);
    }

    #[Test]
    public function it_footer_summary_preview_renders_rich_content_unescaped(): void
    {
        /* Arrange */
        $config = ['summary_content' => '<p>Thank you <strong>very much</strong></p>'];

        /* Act */
        $html = FooterSummaryBrick::toPreviewHtml($config);

        /* Assert */
        $this->assertStringContainsString('<strong>very much</strong>', $html);
        $this->assertStringNotContainsString('&lt;strong&gt;', $html);
    }

    #[Test]
    public function it_detail_invoice_product_brick_reads_invoice_items_only(): void
    {
        /* Arrange */
        $data = [
            'invoice_items' => [['sku' => 'INV-SKU', 'description' => 'Invoice Row', 'quantity' => 1, 'unit_price' => '1.00', 'tax' => '0.00', 'total' => '1.00']],
            'quote_items'   => [['sku' => 'QUOTE-SKU', 'description' => 'Quote Row', 'quantity' => 1, 'unit_price' => '1.00', 'tax' => '0.00', 'total' => '1.00']],
        ];

        /* Act */
        $html = DetailInvoiceProductBrick::toHtml([], $data);

        /* Assert — the shared base class must not accidentally read the sibling brick's data key */
        $this->assertStringContainsString('Invoice Row', $html);
        $this->assertStringNotContainsString('Quote Row', $html);
    }

    #[Test]
    public function it_detail_quote_product_brick_reads_quote_items_only(): void
    {
        /* Arrange */
        $data = [
            'invoice_items' => [['sku' => 'INV-SKU', 'description' => 'Invoice Row', 'quantity' => 1, 'unit_price' => '1.00', 'tax' => '0.00', 'total' => '1.00']],
            'quote_items'   => [['sku' => 'QUOTE-SKU', 'description' => 'Quote Row', 'quantity' => 1, 'unit_price' => '1.00', 'tax' => '0.00', 'total' => '1.00']],
        ];

        /* Act */
        $html = DetailQuoteProductBrick::toHtml([], $data);

        /* Assert */
        $this->assertStringContainsString('Quote Row', $html);
        $this->assertStringNotContainsString('Invoice Row', $html);
    }

    #[Test]
    public function it_all_bricks_have_unique_ids(): void
    {
        /* Arrange */
        $bricks = [
            HeaderCompanyBrick::class,
            HeaderClientBrick::class,
            HeaderInvoiceMetaBrick::class,
            DetailColumnLabelsBrick::class,
            DetailItemsBrick::class,
            FooterTotalsBrick::class,
            FooterNotesBrick::class,
        ];

        /* Act */
        $ids = array_map(fn ($brick) => $brick::getId(), $bricks);

        /* Assert */
        $this->assertCount(7, array_unique($ids));
        $this->assertCount(7, $ids);
    }

    #[Test]
    public function it_all_bricks_return_labels(): void
    {
        /* Arrange */
        $bricks = [
            HeaderCompanyBrick::class,
            HeaderClientBrick::class,
            HeaderInvoiceMetaBrick::class,
            DetailColumnLabelsBrick::class,
            DetailItemsBrick::class,
            FooterTotalsBrick::class,
            FooterNotesBrick::class,
        ];

        /* Act & Assert */
        foreach ($bricks as $brick) {
            $label = $brick::getLabel();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }

    #[Test]
    public function it_all_bricks_return_icons(): void
    {
        /* Arrange */
        $bricks = [
            HeaderCompanyBrick::class,
            HeaderClientBrick::class,
            HeaderInvoiceMetaBrick::class,
            DetailColumnLabelsBrick::class,
            DetailItemsBrick::class,
            FooterTotalsBrick::class,
            FooterNotesBrick::class,
        ];

        /* Act & Assert */
        foreach ($bricks as $brick) {
            $icon = $brick::getIcon();
            $this->assertNotNull($icon);
        }
    }

    #[Test]
    #[DataProvider('widthProvider')]
    public function it_does_not_self_apply_inline_width_percentage_in_preview_templates(string $width): void
    {
        /* Arrange & Act & Assert */
        $fractionalPercentages = ['33.33%', '50%', '66.66%'];

        foreach (ReportBricksCollection::all() as $brickClass) {
            $html = (string) $brickClass::toPreviewHtml(['_width' => $width]);

            foreach ($fractionalPercentages as $percent) {
                $this->assertStringNotContainsString(
                    "width: {$percent}",
                    $html,
                    "Brick [{$brickClass}] should not self-apply width [{$percent}] in preview HTML.",
                );
            }

            $this->assertStringNotContainsString(
                'display: inline-block; vertical-align: top;',
                $html,
                "Brick [{$brickClass}] should not contain outer inline-block width wrapper.",
            );
        }
    }

    #[Test]
    public function it_renders_product_description_hidden_placement(): void
    {
        /* Arrange */
        $config = [
            'description_placement' => FieldPlacement::HIDDEN->value,
        ];
        $data = [
            'invoice_items' => [
                [
                    'sku'         => 'SKU-001',
                    'description' => 'Secret Description Should Not Appear',
                    'quantity'    => 1,
                    'unit_price'  => '50.00',
                    'tax'         => '0.00',
                    'total'       => '50.00',
                ],
            ],
            'items' => [
                [
                    'description' => 'Line Item Secret Description',
                    'quantity'    => 2,
                    'price'       => '25.00',
                    'tax'         => '0.00',
                    'total'       => '50.00',
                ],
            ],
        ];

        /* Act */
        $invoiceHtml = DetailInvoiceProductBrick::toHtml($config, $data);
        $itemsHtml   = DetailItemsBrick::toHtml($config, $data);

        /* Assert */
        $this->assertStringNotContainsString('Secret Description Should Not Appear', (string) $invoiceHtml);
        $this->assertStringNotContainsString('Line Item Secret Description', (string) $itemsHtml);
    }

    #[Test]
    public function it_renders_product_description_inline_column_placement(): void
    {
        /* Arrange */
        $config = [
            'description_placement' => FieldPlacement::INLINE_COLUMN->value,
        ];
        $data = [
            'invoice_items' => [
                [
                    'sku'         => 'SKU-001',
                    'description' => 'Inline Description Text',
                    'quantity'    => 1,
                    'unit_price'  => '50.00',
                    'tax'         => '0.00',
                    'total'       => '50.00',
                ],
            ],
            'items' => [
                [
                    'description' => 'Detail Items Inline Text',
                    'quantity'    => 1,
                    'price'       => '50.00',
                    'tax'         => '0.00',
                    'total'       => '50.00',
                ],
            ],
        ];

        /* Act */
        $invoiceHtml = DetailInvoiceProductBrick::toHtml($config, $data);
        $itemsHtml   = DetailItemsBrick::toHtml($config, $data);

        /* Assert */
        $this->assertStringContainsString('<th align="left">' . trans('ip.description') . '</th>', (string) $invoiceHtml);
        $this->assertStringContainsString('<td>Inline Description Text</td>', (string) $invoiceHtml);
        $this->assertStringContainsString('<th align="left">' . trans('ip.description') . '</th>', (string) $itemsHtml);
        $this->assertStringContainsString('<td>Detail Items Inline Text</td>', (string) $itemsHtml);
        $this->assertStringNotContainsString('colspan=', (string) $invoiceHtml);
        $this->assertStringNotContainsString('colspan=', (string) $itemsHtml);
    }

    #[Test]
    public function it_renders_product_description_below_row_placement(): void
    {
        /* Arrange */
        $config = [
            'description_placement' => FieldPlacement::BELOW_ROW->value,
        ];
        $data = [
            'invoice_items' => [
                [
                    'sku'         => 'SKU-001',
                    'description' => 'Subline Description Text',
                    'quantity'    => 1,
                    'unit_price'  => '50.00',
                    'tax'         => '0.00',
                    'total'       => '50.00',
                ],
            ],
            'items' => [
                [
                    'description' => 'Detail Items Subline Text',
                    'quantity'    => 1,
                    'price'       => '50.00',
                    'tax'         => '0.00',
                    'total'       => '50.00',
                ],
            ],
        ];

        /* Act */
        $invoiceHtml = DetailInvoiceProductBrick::toHtml($config, $data);
        $itemsHtml   = DetailItemsBrick::toHtml($config, $data);

        /* Assert */
        $this->assertStringNotContainsString('<th>' . trans('ip.description') . '</th>', (string) $invoiceHtml);
        $this->assertStringContainsString('colspan="5"', (string) $invoiceHtml);
        $this->assertStringContainsString('Subline Description Text', (string) $invoiceHtml);

        $this->assertStringNotContainsString('<th>' . trans('ip.description') . '</th>', (string) $itemsHtml);
        $this->assertStringContainsString('colspan="4"', (string) $itemsHtml);
        $this->assertStringContainsString('Detail Items Subline Text', (string) $itemsHtml);
    }

    #[Test]
    public function it_calculates_correct_colspan_for_below_row_sub_row_when_toggles_vary(): void
    {
        /* Arrange */
        $data = [
            'invoice_items' => [
                [
                    'sku'         => 'SKU-001',
                    'description' => 'Description with varying columns',
                    'quantity'    => 1,
                    'unit_price'  => '50.00',
                    'tax'         => '0.00',
                    'discount'    => '5.00',
                    'total'       => '45.00',
                ],
            ],
            'items' => [
                [
                    'description' => 'Item description with varying columns',
                    'quantity'    => 1,
                    'price'       => '50.00',
                    'tax'         => '0.00',
                    'total'       => '50.00',
                ],
            ],
        ];

        /* Test invoice product brick with SKU hidden: 4 visible cols (qty, unit_price, tax, total) */
        /* Act */
        $htmlSkuHidden = DetailInvoiceProductBrick::toHtml([
            'description_placement' => FieldPlacement::BELOW_ROW->value,
            'show_sku'              => false,
        ], $data);

        /* Assert */
        $this->assertStringContainsString('colspan="4"', (string) $htmlSkuHidden);

        /* Test invoice product brick with discount shown and SKU hidden: 5 visible cols */
        /* Act */
        $htmlWithDiscount = DetailInvoiceProductBrick::toHtml([
            'description_placement' => FieldPlacement::BELOW_ROW->value,
            'show_sku'              => false,
            'show_discount'         => true,
        ], $data);

        /* Assert */
        $this->assertStringContainsString('colspan="5"', (string) $htmlWithDiscount);

        /* Test invoice product brick with all other columns hidden */
        /* Act */
        $htmlMinimal = DetailInvoiceProductBrick::toHtml([
            'description_placement' => FieldPlacement::BELOW_ROW->value,
            'show_sku'              => false,
            'show_quantity'         => false,
            'show_unit_price'       => false,
            'show_tax'              => false,
            'show_discount'         => false,
            'show_total'            => false,
        ], $data);

        /* Assert */
        $this->assertStringContainsString('colspan="1"', (string) $htmlMinimal);

        /* Test detail items brick with tax hidden: 3 visible cols (qty, price, total) */
        /* Act */
        $htmlItemsTaxHidden = DetailItemsBrick::toHtml([
            'description_placement' => FieldPlacement::BELOW_ROW->value,
            'show_tax'              => false,
        ], $data);

        /* Assert */
        $this->assertStringContainsString('colspan="3"', (string) $htmlItemsTaxHidden);
    }

    #[Test]
    public function it_maintains_backward_compatibility_with_legacy_show_description_boolean(): void
    {
        /* Arrange */
        $data = [
            'invoice_items' => [
                [
                    'sku'         => 'SKU-001',
                    'description' => 'Legacy Description',
                    'quantity'    => 1,
                    'unit_price'  => '50.00',
                    'tax'         => '0.00',
                    'total'       => '50.00',
                ],
            ],
        ];

        /* Act */
        $htmlLegacyTrue   = DetailInvoiceProductBrick::toHtml(['show_description' => true], $data);
        $htmlNewInline    = DetailInvoiceProductBrick::toHtml(['description_placement' => FieldPlacement::INLINE_COLUMN->value], $data);
        $htmlLegacyFalse  = DetailInvoiceProductBrick::toHtml(['show_description' => false], $data);
        $htmlNewHidden    = DetailInvoiceProductBrick::toHtml(['description_placement' => FieldPlacement::HIDDEN->value], $data);
        $htmlDefaultEmpty = DetailInvoiceProductBrick::toHtml([], $data);

        /* Assert */
        $this->assertEquals($htmlNewInline, $htmlLegacyTrue);
        $this->assertEquals($htmlNewHidden, $htmlLegacyFalse);
        $this->assertEquals($htmlNewInline, $htmlDefaultEmpty);
        $this->assertStringContainsString('<td>Legacy Description</td>', (string) $htmlLegacyTrue);
        $this->assertStringNotContainsString('Legacy Description', (string) $htmlLegacyFalse);
    }

    #[Test]
    public function it_renders_multiple_items_with_below_row_descriptions_in_correct_order(): void
    {
        /* Arrange */
        $config = [
            'description_placement' => FieldPlacement::BELOW_ROW->value,
        ];
        $data = [
            'invoice_items' => [
                [
                    'sku'         => 'SKU-001',
                    'description' => 'Description Item 1',
                    'quantity'    => 1,
                    'unit_price'  => '10.00',
                    'tax'         => '0.00',
                    'total'       => '10.00',
                ],
                [
                    'sku'         => 'SKU-002',
                    'description' => 'Description Item 2',
                    'quantity'    => 2,
                    'unit_price'  => '20.00',
                    'tax'         => '0.00',
                    'total'       => '40.00',
                ],
            ],
        ];

        /* Act */
        $html = (string) DetailInvoiceProductBrick::toHtml($config, $data);

        /* Assert — Item 1 main row -> Item 1 description -> Item 2 main row -> Item 2 description */
        $posItem1Sku  = mb_strpos($html, 'SKU-001');
        $posItem1Desc = mb_strpos($html, 'Description Item 1');
        $posItem2Sku  = mb_strpos($html, 'SKU-002');
        $posItem2Desc = mb_strpos($html, 'Description Item 2');

        $this->assertNotFalse($posItem1Sku);
        $this->assertNotFalse($posItem1Desc);
        $this->assertNotFalse($posItem2Sku);
        $this->assertNotFalse($posItem2Desc);

        $this->assertTrue($posItem1Sku < $posItem1Desc, 'Item 1 SKU must precede Item 1 description');
        $this->assertTrue($posItem1Desc < $posItem2Sku, 'Item 1 description must precede Item 2 SKU');
        $this->assertTrue($posItem2Sku < $posItem2Desc, 'Item 2 SKU must precede Item 2 description');
    }
}
