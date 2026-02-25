<?php

namespace Modules\Core\Tests\Unit;

use App\Mason\Bricks\DetailItemsBrick;
use App\Mason\Bricks\FooterNotesBrick;
use App\Mason\Bricks\FooterTotalsBrick;
use App\Mason\Bricks\HeaderClientBrick;
use App\Mason\Bricks\HeaderCompanyBrick;
use App\Mason\Bricks\HeaderInvoiceMetaBrick;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class MasonBricksTest extends AbstractTestCase
{
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
            'show_phone' => true,
            'font_size' => 10,
        ];

        /* Act */
        $html = HeaderCompanyBrick::toPreviewHtml($config);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('Company Name', $html);
    }

    #[Test]
    public function it_header_company_brick_generates_render_html(): void
    {
        /* Arrange */
        $config = ['show_vat_id' => true];
        $data = [
            'company' => [
                'name' => 'Test Company',
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
        $data = [
            'client' => [
                'name' => 'Test Client',
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
            'show_invoice_date' => true,
            'show_due_date' => false,
        ];
        $data = [
            'invoice' => [
                'number' => 'INV-001',
                'date' => '2024-01-01',
            ],
        ];

        /* Act */
        $html = HeaderInvoiceMetaBrick::toHtml($config, $data);

        /* Assert */
        $this->assertIsString($html);
        $this->assertStringContainsString('INV-001', $html);
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
            'show_quantity' => true,
            'show_price' => true,
        ];
        $data = [
            'items' => [
                [
                    'description' => 'Item 1',
                    'quantity' => 2,
                    'price' => '100.00',
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
            'show_tax' => true,
            'show_total' => true,
        ];
        $data = [
            'totals' => [
                'subtotal' => '100.00',
                'tax' => '10.00',
                'total' => '110.00',
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
    public function it_all_bricks_have_unique_ids(): void
    {
        /* Arrange */
        $bricks = [
            HeaderCompanyBrick::class,
            HeaderClientBrick::class,
            HeaderInvoiceMetaBrick::class,
            DetailItemsBrick::class,
            FooterTotalsBrick::class,
            FooterNotesBrick::class,
        ];

        /* Act */
        $ids = array_map(fn($brick) => $brick::getId(), $bricks);

        /* Assert */
        $this->assertCount(6, array_unique($ids));
        $this->assertCount(6, $ids);
    }

    #[Test]
    public function it_all_bricks_return_labels(): void
    {
        /* Arrange */
        $bricks = [
            HeaderCompanyBrick::class,
            HeaderClientBrick::class,
            HeaderInvoiceMetaBrick::class,
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
}
