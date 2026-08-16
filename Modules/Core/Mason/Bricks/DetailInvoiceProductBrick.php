<?php

namespace Modules\Core\Mason\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Modules\Core\Enums\ReportBlockWidth;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Mason\ReportBrick;

class DetailInvoiceProductBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'detail_invoice_product';
    }

    public static function getLabel(): string
    {
        return trans('ip.invoice_product_details');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.invoice_product_details');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.detail-invoice-product.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.detail-invoice-product.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_invoice_product_details'))
            ->modalHeading(trans('ip.invoice_product_details_settings'))
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
