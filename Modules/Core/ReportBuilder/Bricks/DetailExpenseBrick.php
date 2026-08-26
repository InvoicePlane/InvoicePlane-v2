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

class DetailExpenseBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'detail_expense';
    }

    public static function getLabel(): string
    {
        return trans('ip.expense_details');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.expense_details');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('core::report-builder.bricks.detail-expense.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('core::report-builder.bricks.detail-expense.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_expense_details'))
            ->modalHeading(trans('ip.expense_details_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
                Checkbox::make('show_expense_number')
                    ->label(trans('ip.show_expense_number'))
                    ->default(true),
                Checkbox::make('show_expense_date')
                    ->label(trans('ip.show_expense_date'))
                    ->default(true),
                Checkbox::make('show_category')
                    ->label(trans('ip.show_category'))
                    ->default(true),
                Checkbox::make('show_vendor')
                    ->label(trans('ip.show_vendor'))
                    ->default(false),
                Checkbox::make('show_description')
                    ->label(trans('ip.show_description'))
                    ->default(true),
                Checkbox::make('show_amount')
                    ->label(trans('ip.show_amount'))
                    ->default(true),
                Checkbox::make('show_status')
                    ->label(trans('ip.show_status'))
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
