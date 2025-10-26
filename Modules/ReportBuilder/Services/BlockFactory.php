<?php

namespace Modules\ReportBuilder\Services;

use InvalidArgumentException;
use Modules\ReportBuilder\Handlers\DetailItemsBlockHandler;
use Modules\ReportBuilder\Handlers\DetailItemTaxBlockHandler;
use Modules\ReportBuilder\Handlers\FooterNotesBlockHandler;
use Modules\ReportBuilder\Handlers\FooterQrCodeBlockHandler;
use Modules\ReportBuilder\Handlers\FooterTotalsBlockHandler;
use Modules\ReportBuilder\Handlers\HeaderClientBlockHandler;
use Modules\ReportBuilder\Handlers\HeaderCompanyBlockHandler;
use Modules\ReportBuilder\Handlers\HeaderInvoiceMetaBlockHandler;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;

/**
 * Factory for creating block handlers.
 *
 * Provides static methods to instantiate the appropriate handler
 * for a given block type.
 */
class BlockFactory
{
    /**
     * Create a block handler by type.
     *
     * @param string $type The block type
     *
     * @return BlockHandlerInterface The handler instance
     *
     * @throws InvalidArgumentException If the block type is not supported
     */
    public static function make(string $type): BlockHandlerInterface
    {
        return match ($type) {
            'header_company'       => new HeaderCompanyBlockHandler(),
            'header_client'        => new HeaderClientBlockHandler(),
            'header_invoice_meta'  => new HeaderInvoiceMetaBlockHandler(),
            'detail_items'         => new DetailItemsBlockHandler(),
            'detail_item_tax'      => new DetailItemTaxBlockHandler(),
            'footer_totals'        => new FooterTotalsBlockHandler(),
            'footer_notes'         => new FooterNotesBlockHandler(),
            'footer_qr_code'       => new FooterQrCodeBlockHandler(),
            default                => throw new InvalidArgumentException("Unsupported block type: {$type}"),
        };
    }

    /**
     * Get all available block types with metadata.
     *
     * @return array Array of block type metadata
     */
    public static function all(): array
    {
        return [
            [
                'type'        => 'header_company',
                'label'       => 'Company Header',
                'category'    => 'header',
                'description' => 'Display company information including name, VAT, phone, and address',
                'icon'        => 'building',
            ],
            [
                'type'        => 'header_client',
                'label'       => 'Client Header',
                'category'    => 'header',
                'description' => 'Display client/customer information',
                'icon'        => 'user',
            ],
            [
                'type'        => 'header_invoice_meta',
                'label'       => 'Invoice Metadata',
                'category'    => 'header',
                'description' => 'Display invoice number, date, due date, and status',
                'icon'        => 'file-text',
            ],
            [
                'type'        => 'detail_items',
                'label'       => 'Invoice Items',
                'category'    => 'detail',
                'description' => 'Display line items with quantity, price, and subtotal',
                'icon'        => 'list',
            ],
            [
                'type'        => 'detail_item_tax',
                'label'       => 'Item Tax Details',
                'category'    => 'detail',
                'description' => 'Display tax breakdown by tax rate',
                'icon'        => 'percent',
            ],
            [
                'type'        => 'footer_totals',
                'label'       => 'Invoice Totals',
                'category'    => 'footer',
                'description' => 'Display subtotal, tax, discount, and total amounts',
                'icon'        => 'calculator',
            ],
            [
                'type'        => 'footer_notes',
                'label'       => 'Footer Notes',
                'category'    => 'footer',
                'description' => 'Display terms, conditions, and footer text',
                'icon'        => 'message-square',
            ],
            [
                'type'        => 'footer_qr_code',
                'label'       => 'QR Code',
                'category'    => 'footer',
                'description' => 'Display QR code linking to invoice',
                'icon'        => 'qr-code',
            ],
        ];
    }
}
