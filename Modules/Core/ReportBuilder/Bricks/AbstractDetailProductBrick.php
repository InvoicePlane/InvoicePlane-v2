<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

    public static function toPreviewHtml(array $config): ?string
    {
        return view('core::report-builder.bricks.' . static::viewSlug() . '.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
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
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
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
            ]);
    }
}
