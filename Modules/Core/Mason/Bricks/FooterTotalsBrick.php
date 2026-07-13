<?php

namespace Modules\Core\Mason\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Mason\ReportBrick;

class FooterTotalsBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'footer_totals';
    }

    public static function getLabel(): string
    {
        return trans('ip.totals_section');
    }

    public static function getIcon(): string | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.totals_section');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.footer-totals.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.footer-totals.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_totals'))
            ->modalHeading(trans('ip.totals_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Checkbox::make('show_subtotal')
                    ->label(trans('ip.show_subtotal'))
                    ->default(true),
                Checkbox::make('show_tax')
                    ->label(trans('ip.show_tax'))
                    ->default(true),
                Checkbox::make('show_total')
                    ->label(trans('ip.show_total'))
                    ->default(true),
                Checkbox::make('show_paid')
                    ->label(trans('ip.show_paid'))
                    ->default(false),
                Checkbox::make('show_balance')
                    ->label(trans('ip.show_balance'))
                    ->default(false),
                Checkbox::make('highlight_total')
                    ->label(trans('ip.highlight_total'))
                    ->default(true),
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(10)
                    ->minValue(8)
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
