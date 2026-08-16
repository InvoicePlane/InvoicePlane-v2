<?php

namespace Modules\Core\Mason\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Mason\ReportBrick;

class HeaderCompanyBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'header_company';
    }

    public static function getLabel(): string
    {
        return trans('ip.company_header');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M9 8h1"/><path d="M9 12h1"/><path d="M9 16h1"/><path d="M14 8h1"/><path d="M14 12h1"/><path d="M14 16h1"/><path d="M6 4h12v17H6z"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.company_header');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.header-company.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.header-company.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_company_header'))
            ->modalHeading(trans('ip.company_header_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Checkbox::make('show_vat_id')
                    ->label(trans('ip.show_vat_id'))
                    ->default(true),
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
                Select::make('font_weight')
                    ->label(trans('ip.font_weight'))
                    ->options([
                        'normal' => trans('ip.font_weight_normal'),
                        'bold'   => trans('ip.font_weight_bold'),
                    ])
                    ->default('bold'),
                Select::make('text_align')
                    ->label(trans('ip.text_align'))
                    ->options([
                        'left'   => trans('ip.align_left'),
                        'center' => trans('ip.align_center'),
                        'right'  => trans('ip.align_right'),
                    ])
                    ->default('left'),
            ]);
    }
}
