<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\ReportBuilder\ReportBrick;

/**
 * Shared shape for the footer rich-text bricks (notes/terms/summary): each
 * falls back from its own configured RichEditor content to the document's
 * matching text field, and offers the same _width + font_size controls
 * alongside one RichEditor field. Subclasses only supply the bits that
 * actually differ — id, labels, view slug, and the content field's name.
 */
abstract class AbstractFooterTextBrick extends ReportBrick
{
    /**
     * The Blade view directory under report-builder.bricks.* (e.g. 'footer-notes').
     */
    abstract protected static function viewSlug(): string;

    /**
     * The RichEditor/config field name (e.g. 'footer_content').
     */
    abstract protected static function contentField(): string;

    abstract protected static function labelKey(): string;

    abstract protected static function configureLabelKey(): string;

    abstract protected static function modalHeadingKey(): string;

    abstract protected static function contentLabelKey(): string;

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
        [$min, $max] = static::fontSizeRange();

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
                RichEditor::make(static::contentField())
                    ->label(trans(static::contentLabelKey()))
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'bulletList',
                        'orderedList',
                    ]),
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(static::defaultFontSize())
                    ->minValue($min)
                    ->maxValue($max),
            ]);
    }

    protected static function defaultFontSize(): int
    {
        return 8;
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected static function fontSizeRange(): array
    {
        return [6, 12];
    }
}
