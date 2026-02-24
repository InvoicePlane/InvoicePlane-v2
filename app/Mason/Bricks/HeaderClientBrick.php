<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class HeaderClientBrick extends Brick
{
    public static function getId(): string
    {
        return 'header_client';
    }

    public static function getLabel(): string
    {
        return trans('ip.client_header');
    }

    public static function getIcon(): string | Htmlable | null
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

    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason.bricks.header-client.index', [
            'config' => $config,
            'data' => $data,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_client_header'))
            ->modalHeading(trans('ip.client_header_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'show_phone' => $arguments['show_phone'] ?? true,
                'show_email' => $arguments['show_email'] ?? true,
                'show_address' => $arguments['show_address'] ?? true,
                'font_size' => $arguments['font_size'] ?? 10,
                'text_align' => $arguments['text_align'] ?? 'right',
            ])
            ->schema([
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
                        'left' => trans('ip.align_left'),
                        'center' => trans('ip.align_center'),
                        'right' => trans('ip.align_right'),
                    ])
                    ->default('right'),
            ])
            ->action(function (array $arguments, array $data, \Awcodes\Mason\Mason $component) {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return;
                }

                $brickContent = [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'config' => $data,
                        'id' => $arguments['id'],
                        'label' => $brick::getPreviewLabel($data),
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
