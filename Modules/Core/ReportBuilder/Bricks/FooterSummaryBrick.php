<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class FooterSummaryBrick extends AbstractFooterTextBrick
{
    public static function getId(): string
    {
        return 'footer_summary';
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>');
    }

    protected static function viewSlug(): string
    {
        return 'footer-summary';
    }

    protected static function contentField(): string
    {
        return 'summary_content';
    }

    protected static function labelKey(): string
    {
        return 'ip.summary';
    }

    protected static function configureLabelKey(): string
    {
        return 'ip.configure_summary';
    }

    protected static function modalHeadingKey(): string
    {
        return 'ip.summary_settings';
    }

    protected static function contentLabelKey(): string
    {
        return 'ip.summary_content';
    }

    protected static function defaultFontSize(): int
    {
        return 9;
    }

    protected static function fontSizeRange(): array
    {
        return [6, 14];
    }
}
