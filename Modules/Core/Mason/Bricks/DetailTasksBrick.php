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

class DetailTasksBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'detail_tasks';
    }

    public static function getLabel(): string
    {
        return trans('ip.tasks_table');
    }

    public static function getIcon(): string|Htmlable|null
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

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.detail-tasks.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_tasks'))
            ->modalHeading(trans('ip.tasks_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
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
                        'bold'   => trans('ip.bold'),
                        'italic' => trans('ip.italic'),
                    ])
                    ->default('bold'),
            ]);
    }
}
