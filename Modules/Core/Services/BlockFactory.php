<?php

namespace Modules\Core\Services;

use InvalidArgumentException;
use Modules\Core\Handlers\DetailItemsBlockHandler;
use Modules\Core\Handlers\DetailItemTaxBlockHandler;
use Modules\Core\Handlers\FooterNotesBlockHandler;
use Modules\Core\Handlers\FooterQrCodeBlockHandler;
use Modules\Core\Handlers\FooterTotalsBlockHandler;
use Modules\Core\Handlers\HeaderClientBlockHandler;
use Modules\Core\Handlers\HeaderCompanyBlockHandler;
use Modules\Core\Handlers\HeaderInvoiceMetaBlockHandler;
use Modules\Core\Interfaces\BlockHandlerInterface;

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
            'company_header'      => app(HeaderCompanyBlockHandler::class),
            'header_client'       => app(HeaderClientBlockHandler::class),
            'header_invoice_meta' => app(HeaderInvoiceMetaBlockHandler::class),
            'detail_items'        => app(DetailItemsBlockHandler::class),
            'detail_item_tax'     => app(DetailItemTaxBlockHandler::class),
            'footer_totals'       => app(FooterTotalsBlockHandler::class),
            'footer_notes'        => app(FooterNotesBlockHandler::class),
            'footer_qr_code'      => app(FooterQrCodeBlockHandler::class),
            default               => throw new InvalidArgumentException("Unsupported block type: {$type}"),
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
                'type'        => 'company_header',
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
