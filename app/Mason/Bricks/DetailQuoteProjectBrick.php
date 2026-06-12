<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class DetailQuoteProjectBrick extends Brick
{
    public static function getId(): string
    {
        return 'detail_quote_project';
    }

    public static function getLabel(): string
    {
        return trans('ip.quote_project_details');
    }

    public static function getIcon(): string | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.quote_project_details');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.detail-quote-project.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason.bricks.detail-quote-project.index', [
            'config' => $config,
            'data'   => $data,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_quote_project_details'))
            ->modalHeading(trans('ip.quote_project_details_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Checkbox::make('show_project_name')
                    ->label(trans('ip.show_project_name'))
                    ->default(true),
                Checkbox::make('show_task_name')
                    ->label(trans('ip.show_task_name'))
                    ->default(true),
                Checkbox::make('show_description')
                    ->label(trans('ip.show_description'))
                    ->default(true),
                Checkbox::make('show_hours')
                    ->label(trans('ip.show_hours'))
                    ->default(true),
                Checkbox::make('show_rate')
                    ->label(trans('ip.show_rate'))
                    ->default(true),
                Checkbox::make('show_total')
                    ->label(trans('ip.show_total'))
                    ->default(true),
                Checkbox::make('group_by_project')
                    ->label(trans('ip.group_by_project'))
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
