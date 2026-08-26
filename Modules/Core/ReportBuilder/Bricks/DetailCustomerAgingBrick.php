<?php

namespace Modules\Core\ReportBuilder\Bricks;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Modules\Core\Enums\ReportBlockWidth;
use Modules\Core\ReportBuilder\ReportBrick;

class DetailCustomerAgingBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'detail_customer_aging';
    }

    public static function getLabel(): string
    {
        return trans('ip.customer_aging_details');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.customer_aging_details');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('core::report-builder.bricks.detail-customer-aging.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('core::report-builder.bricks.detail-customer-aging.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_customer_aging'))
            ->modalHeading(trans('ip.customer_aging_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
                Checkbox::make('show_invoice_number')
                    ->label(trans('ip.show_invoice_number'))
                    ->default(true),
                Checkbox::make('show_invoice_date')
                    ->label(trans('ip.show_invoice_date'))
                    ->default(true),
                Checkbox::make('show_due_date')
                    ->label(trans('ip.show_due_date'))
                    ->default(true),
                Checkbox::make('show_current')
                    ->label(trans('ip.show_current'))
                    ->default(true),
                Checkbox::make('show_30_days')
                    ->label(trans('ip.show_30_days'))
                    ->default(true),
                Checkbox::make('show_60_days')
                    ->label(trans('ip.show_60_days'))
                    ->default(true),
                Checkbox::make('show_90_days')
                    ->label(trans('ip.show_90_days'))
                    ->default(true),
                Checkbox::make('show_over_90_days')
                    ->label(trans('ip.show_over_90_days'))
                    ->default(true),
                Checkbox::make('show_total_due')
                    ->label(trans('ip.show_total_due'))
                    ->default(true),
                Checkbox::make('highlight_overdue')
                    ->label(trans('ip.highlight_overdue'))
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
