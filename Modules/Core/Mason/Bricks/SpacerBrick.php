<?php

namespace Modules\Core\Mason\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Modules\Core\Enums\ReportBlockWidth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Mason\ReportBrick;

class SpacerBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'spacer';
    }

    public static function getLabel(): string
    {
        return trans('ip.spacer');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22v-6"/><path d="M12 8V2"/><path d="M4 12H2"/><path d="M10 12H8"/><path d="M16 12h-2"/><path d="M22 12h-2"/><path d="m15 19-3 3-3-3"/><path d="m15 5-3-3-3 3"/></svg>');
    }

    public static function allowedBands(): array
    {
        return ReportBand::cases();
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.spacer');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.spacer.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.spacer.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_spacer'))
            ->modalHeading(trans('ip.spacer_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
                TextInput::make('height')
                    ->label(trans('ip.spacer_height'))
                    ->numeric()
                    ->default(20)
                    ->minValue(1)
                    ->maxValue(500),
            ]);
    }
}
