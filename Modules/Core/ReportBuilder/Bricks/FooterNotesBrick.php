<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class FooterNotesBrick extends AbstractFooterTextBrick
{
    public static function getId(): string
    {
        return 'footer_notes';
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>');
    }

    protected static function viewSlug(): string
    {
        return 'footer-notes';
    }

    protected static function contentField(): string
    {
        return 'footer_content';
    }

    protected static function labelKey(): string
    {
        return 'ip.footer';
    }

    protected static function configureLabelKey(): string
    {
        return 'ip.configure_notes';
    }

    protected static function modalHeadingKey(): string
    {
        return 'ip.notes_settings';
    }

    protected static function contentLabelKey(): string
    {
        return 'ip.footer_content';
    }
}
