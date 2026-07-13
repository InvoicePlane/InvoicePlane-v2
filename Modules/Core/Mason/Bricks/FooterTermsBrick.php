<?php

namespace Modules\Core\Mason\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Mason\ReportBrick;

class FooterTermsBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'footer_terms';
    }

    public static function getLabel(): string
    {
        return trans('ip.terms_conditions');
    }

    public static function getIcon(): string | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.terms_conditions');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.footer-terms.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.footer-terms.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_terms'))
            ->modalHeading(trans('ip.terms_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                RichEditor::make('terms_content')
                    ->label(trans('ip.terms_content'))
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'bulletList',
                        'orderedList',
                    ]),
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(8)
                    ->minValue(6)
                    ->maxValue(12),
            ]);
    }
}
