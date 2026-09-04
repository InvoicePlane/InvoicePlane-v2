<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\ReportTemplateType;

class DetailInvoiceProductBrick extends AbstractDetailProductBrick
{
    public static function getId(): string
    {
        return 'detail_invoice_product';
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>');
    }

    public static function allowedTypes(): array
    {
        return [ReportTemplateType::INVOICE];
    }

    protected static function viewSlug(): string
    {
        return 'detail-invoice-product';
    }

    protected static function labelKey(): string
    {
        return 'ip.invoice_product_details';
    }

    protected static function configureLabelKey(): string
    {
        return 'ip.configure_invoice_product_details';
    }

    protected static function modalHeadingKey(): string
    {
        return 'ip.invoice_product_details_settings';
    }
}
