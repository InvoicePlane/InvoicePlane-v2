<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class HeaderProjectBrick extends Brick
{
    public static function getId(): string
    {
        return 'header_project';
    }

    public static function getLabel(): string
    {
        return trans('ip.project_header');
    }

    public static function getIcon(): string | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 12h8M8 16h5"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.project_header');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.header-project.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason.bricks.header-project.index', [
            'config' => $config,
            'data'   => $data,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_project'))
            ->modalHeading(trans('ip.project_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Checkbox::make('show_project_number')
                    ->label(trans('ip.show_project_number'))
                    ->default(true),
                Checkbox::make('show_project_name')
                    ->label(trans('ip.show_project_name'))
                    ->default(true),
                Checkbox::make('show_start_date')
                    ->label(trans('ip.show_start_date'))
                    ->default(true),
                Checkbox::make('show_end_date')
                    ->label(trans('ip.show_end_date'))
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
                    ->default('left'),
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
