<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\ReportTemplateType;

class DetailQuoteProductBrick extends AbstractDetailProductBrick
{
    public static function getId(): string
    {
        return 'detail_quote_product';
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>');
    }

    public static function allowedTypes(): array
    {
        return [ReportTemplateType::QUOTE];
    }

    protected static function viewSlug(): string
    {
        return 'detail-quote-product';
    }

    protected static function labelKey(): string
    {
        return 'ip.quote_product_details';
    }

    protected static function configureLabelKey(): string
    {
        return 'ip.configure_quote_product_details';
    }

    protected static function modalHeadingKey(): string
    {
        return 'ip.quote_product_details_settings';
    }
}
