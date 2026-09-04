<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class FooterTermsBrick extends AbstractFooterTextBrick
{
    public static function getId(): string
    {
        return 'footer_terms';
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>');
    }

    protected static function viewSlug(): string
    {
        return 'footer-terms';
    }

    protected static function contentField(): string
    {
        return 'terms_content';
    }

    protected static function labelKey(): string
    {
        return 'ip.terms_conditions';
    }

    protected static function configureLabelKey(): string
    {
        return 'ip.configure_terms';
    }

    protected static function modalHeadingKey(): string
    {
        return 'ip.terms_settings';
    }

    protected static function contentLabelKey(): string
    {
        return 'ip.terms_content';
    }
}
