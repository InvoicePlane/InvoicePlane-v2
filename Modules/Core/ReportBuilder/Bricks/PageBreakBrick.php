<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\ReportBand;
use Modules\Core\ReportBuilder\ReportBrick;

class PageBreakBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'page_break';
    }

    public static function getLabel(): string
    {
        return trans('ip.page_break');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v7"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M20 8v3"/><path d="M4 15v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M2 13h4"/><path d="M10 13h4"/><path d="M18 13h4"/></svg>');
    }

    public static function allowedBands(): array
    {
        return ReportBand::cases();
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.page_break');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('core::report-builder.bricks.page-break.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('core::report-builder.bricks.page-break.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->modalHidden();
    }
}
