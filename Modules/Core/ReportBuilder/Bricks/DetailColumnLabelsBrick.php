<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\ReportBand;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\ReportBuilder\ReportBrick;

class DetailColumnLabelsBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'detail_column_labels';
    }

    public static function getLabel(): string
    {
        return trans('ip.column_labels');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3zM3 9h18M9 21V9"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.column_labels');
    }

    /**
     * @return array<ReportBand>
     */
    public static function allowedBands(): array
    {
        return [
            ReportBand::HEADER,
            ReportBand::GROUP_HEADER,
            ReportBand::DETAILS,
        ];
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('core::report-builder.bricks.detail-column-labels.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('core::report-builder.bricks.detail-column-labels.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_column_labels'))
            ->modalHeading(trans('ip.column_labels_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
                Checkbox::make('show_sku')
                    ->label(trans('ip.show_sku'))
                    ->default(false),
                Checkbox::make('show_description')
                    ->label(trans('ip.show_description'))
                    ->default(true),
                Checkbox::make('show_quantity')
                    ->label(trans('ip.show_quantity'))
                    ->default(true),
                Checkbox::make('show_price')
                    ->label(trans('ip.show_price'))
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
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(9)
                    ->minValue(7)
                    ->maxValue(14),
            ]);
    }
}
