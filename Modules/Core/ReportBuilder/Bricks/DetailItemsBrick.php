<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\FieldPlacement;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\ReportBuilder\ReportBrick;

class DetailItemsBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'detail_items';
    }

    public static function getLabel(): string
    {
        return trans('ip.line_items_table');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.line_items_table');
    }

    public static function normalizeLegacyConfig(array $config): array
    {
        if ( ! isset($config['description_placement']) && isset($config['show_description'])) {
            $config['description_placement'] = $config['show_description']
                ? FieldPlacement::INLINE_COLUMN->value
                : FieldPlacement::HIDDEN->value;
        }

        return $config;
    }

    public static function filterConfig(array $config): array
    {
        $config = static::normalizeLegacyConfig($config);

        return parent::filterConfig($config);
    }

    public static function toPreviewHtml(array $config): ?string
    {
        $config = static::normalizeLegacyConfig($config);

        return view('core::report-builder.bricks.detail-items.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        $config = static::normalizeLegacyConfig($config);

        return view('core::report-builder.bricks.detail-items.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_line_items'))
            ->modalHeading(trans('ip.line_items_settings'))
            ->slideOver()
            ->fillForm(function (array $arguments): ?array {
                $config = $arguments['config'] ?? null;
                if ($config !== null) {
                    $config = static::normalizeLegacyConfig($config);
                }

                return $config;
            })
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
                Select::make('description_placement')
                    ->label(trans('ip.description_placement'))
                    ->options(collect(FieldPlacement::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()]))
                    ->default(FieldPlacement::INLINE_COLUMN->value),
                Checkbox::make('show_quantity')
                    ->label(trans('ip.show_quantity'))
                    ->default(true),
                Checkbox::make('show_price')
                    ->label(trans('ip.show_price'))
                    ->default(true),
                Checkbox::make('show_tax')
                    ->label(trans('ip.show_tax'))
                    ->default(true),
                Checkbox::make('show_total')
                    ->label(trans('ip.show_total'))
                    ->default(true),
                Checkbox::make('show_table_header')
                    ->label(trans('ip.show_table_header'))
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
            ]);
    }
}
