<?php

namespace Modules\Core\Mason\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Modules\Core\Enums\ReportBlockWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Mason\ReportBrick;

class HeaderQuoteMetaBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'header_quote_meta';
    }

    public static function getLabel(): string
    {
        return trans('ip.quote_metadata');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.quote_metadata');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.header-quote-meta.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.header-quote-meta.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_quote_meta'))
            ->modalHeading(trans('ip.quote_meta_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
                Checkbox::make('show_quote_number')
                    ->label(trans('ip.show_quote_number'))
                    ->default(true),
                Checkbox::make('show_quoted_at')
                    ->label(trans('ip.show_quoted_at'))
                    ->default(true),
                Checkbox::make('show_expires_at')
                    ->label(trans('ip.show_expires_at'))
                    ->default(true),
                Checkbox::make('show_status')
                    ->label(trans('ip.show_status'))
                    ->default(true),
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(10)
                    ->minValue(6)
                    ->maxValue(16),
                Select::make('text_align')
                    ->label(trans('ip.text_align'))
                    ->options([
                        'left'   => trans('ip.align_left'),
                        'center' => trans('ip.align_center'),
                        'right'  => trans('ip.align_right'),
                    ])
                    ->default('right'),
            ]);
    }
}
