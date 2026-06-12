<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class DetailQuoteProductBrick extends Brick
{
    public static function getId(): string
    {
        return 'detail_quote_product';
    }

    public static function getLabel(): string
    {
        return trans('ip.quote_product_details');
    }

    public static function getIcon(): string | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.quote_product_details');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.detail-quote-product.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason.bricks.detail-quote-product.index', [
            'config' => $config,
            'data'   => $data,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_quote_product_details'))
            ->modalHeading(trans('ip.quote_product_details_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Checkbox::make('show_sku')
                    ->label(trans('ip.show_sku'))
                    ->default(true),
                Checkbox::make('show_description')
                    ->label(trans('ip.show_description'))
                    ->default(true),
                Checkbox::make('show_quantity')
                    ->label(trans('ip.show_quantity'))
                    ->default(true),
                Checkbox::make('show_unit_price')
                    ->label(trans('ip.show_unit_price'))
                    ->default(true),
                Checkbox::make('show_tax')
                    ->label(trans('ip.show_tax'))
                    ->default(true),
                Checkbox::make('show_discount')
                    ->label(trans('ip.show_discount'))
                    ->default(false),
                Checkbox::make('show_total')
                    ->label(trans('ip.show_total'))
                    ->default(true),
                Checkbox::make('alternating_rows')
                    ->label(trans('ip.alternating_rows'))
                    ->default(true),
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(9)
                    ->minValue(7)
                    ->maxValue(14),
            ])
            ->action(function (array $arguments, array $data, \Awcodes\Mason\Mason $component) {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return;
                }

                $brickContent = [
                    'type'  => 'masonBrick',
                    'attrs' => [
                        'config'  => $data,
                        'id'      => $arguments['id'],
                        'label'   => $brick::getPreviewLabel($data),
                        'preview' => base64_encode($brick::toPreviewHtml($data)),
                    ],
                ];

                $component->runCommands([
                    \Awcodes\Mason\Actions\EditorCommand::make(
                        'insertContentAt',
                        arguments: [
                            $arguments['dragPosition'],
                            $brickContent,
                        ],
                    ),
                ]);
            });
    }
}
