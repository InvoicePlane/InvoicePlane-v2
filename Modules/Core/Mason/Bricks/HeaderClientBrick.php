<?php

namespace Modules\Core\Mason\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\Mason\ReportBrick;

class HeaderClientBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'header_client';
    }

    public static function getLabel(): string
    {
        return trans('ip.client_header');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.client_header');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.header-client.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.header-client.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_client_header'))
            ->modalHeading(trans('ip.client_header_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
                Checkbox::make('show_phone')
                    ->label(trans('ip.show_phone'))
                    ->default(true),
                Checkbox::make('show_email')
                    ->label(trans('ip.show_email'))
                    ->default(true),
                Checkbox::make('show_address')
                    ->label(trans('ip.show_address'))
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
