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

class DetailInvoiceProjectBrick extends ReportBrick
{
    public static function getId(): string
    {
        return 'detail_invoice_project';
    }

    public static function getLabel(): string
    {
        return trans('ip.invoice_project_details');
    }

    public static function getIcon(): string|Htmlable|null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h10M7 11h10M7 7h10"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.invoice_project_details');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('core::report-builder.bricks.detail-invoice-project.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('core::report-builder.bricks.detail-invoice-project.index', [
            'config' => $config,
            'data'   => $data ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_invoice_project_details'))
            ->modalHeading(trans('ip.invoice_project_details_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            ->schema([
                Select::make('_width')
                    ->label(trans('ip.width'))
                    ->options(collect(ReportBlockWidth::cases())->mapWithKeys(fn ($case) => [$case->value => trans("ip.{$case->value}_width")]))
                    ->default(ReportBlockWidth::FULL->value),
                Checkbox::make('show_project_name')
                    ->label(trans('ip.show_project_name'))
                    ->default(true),
                Checkbox::make('show_task_name')
                    ->label(trans('ip.show_task_name'))
                    ->default(true),
                Checkbox::make('show_description')
                    ->label(trans('ip.show_description'))
                    ->default(true),
                Checkbox::make('show_hours')
                    ->label(trans('ip.show_hours'))
                    ->default(true),
                Checkbox::make('show_rate')
                    ->label(trans('ip.show_rate'))
                    ->default(true),
                Checkbox::make('show_total')
                    ->label(trans('ip.show_total'))
                    ->default(true),
                Checkbox::make('group_by_project')
                    ->label(trans('ip.group_by_project'))
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
