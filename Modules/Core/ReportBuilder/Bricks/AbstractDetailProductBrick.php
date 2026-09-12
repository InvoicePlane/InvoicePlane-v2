<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Modules\Core\Enums\FieldPlacement;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\ReportBuilder\ReportBrick;

/**
 * Shared shape for the per-document-type product detail bricks
 * (DetailInvoiceProductBrick, DetailQuoteProductBrick): same columns, same
 * config schema — only the id, labels, view slug, icon, allowed document
 * type, and the data key they read differ per document type.
 */
abstract class AbstractDetailProductBrick extends ReportBrick
{
    /**
     * The Blade view directory under report-builder.bricks.* (e.g. 'detail-invoice-product').
     */
    abstract protected static function viewSlug(): string;

    abstract protected static function labelKey(): string;

    abstract protected static function configureLabelKey(): string;

    abstract protected static function modalHeadingKey(): string;

    public static function getLabel(): string
    {
        return trans(static::labelKey());
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans(static::labelKey());
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

        return view('core::report-builder.bricks.' . static::viewSlug() . '.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        $config = static::normalizeLegacyConfig($config);

        return view('core::report-builder.bricks.' . static::viewSlug() . '.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans(static::configureLabelKey()))
            ->modalHeading(trans(static::modalHeadingKey()))
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
                Checkbox::make('show_sku')
                    ->label(trans('ip.show_sku'))
                    ->default(true),
                Select::make('description_placement')
                    ->label(trans('ip.description_placement'))
                    ->options(collect(FieldPlacement::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()]))
                    ->default(FieldPlacement::INLINE_COLUMN->value),
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
