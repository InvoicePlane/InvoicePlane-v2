<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class DetailTasksBrick extends Brick
{
    public static function getId(): string
    {
        return 'detail_tasks';
    }

    public static function getLabel(): string
    {
        return trans('ip.tasks_table');
    }

    public static function getIcon(): string | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.tasks_table');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.detail-tasks.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason.bricks.detail-tasks.index', [
            'config' => $config,
            'data' => $data,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_tasks'))
            ->modalHeading(trans('ip.tasks_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'show_task_number' => $arguments['show_task_number'] ?? true,
                'show_task_name' => $arguments['show_task_name'] ?? true,
                'show_description' => $arguments['show_description'] ?? true,
                'show_due_at' => $arguments['show_due_at'] ?? false,
                'show_task_price' => $arguments['show_task_price'] ?? true,
                'show_task_status' => $arguments['show_task_status'] ?? true,
                'font_size' => $arguments['font_size'] ?? 9,
                'header_style' => $arguments['header_style'] ?? 'bold',
            ])
            ->schema([
                Checkbox::make('show_task_number')
                    ->label(trans('ip.show_task_number'))
                    ->default(true),
                Checkbox::make('show_task_name')
                    ->label(trans('ip.show_task_name'))
                    ->default(true),
                Checkbox::make('show_description')
                    ->label(trans('ip.show_description'))
                    ->default(true),
                Checkbox::make('show_due_at')
                    ->label(trans('ip.show_due_at'))
                    ->default(false),
                Checkbox::make('show_task_price')
                    ->label(trans('ip.show_task_price'))
                    ->default(true),
                Checkbox::make('show_task_status')
                    ->label(trans('ip.show_task_status'))
                    ->default(true),
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(9)
                    ->minValue(6)
                    ->maxValue(12),
                Select::make('header_style')
                    ->label(trans('ip.header_style'))
                    ->options([
                        'normal' => trans('ip.normal'),
                        'bold' => trans('ip.bold'),
                        'italic' => trans('ip.italic'),
                    ])
                    ->default('bold'),
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
