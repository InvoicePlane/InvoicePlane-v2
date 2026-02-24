<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class HeaderInvoiceMetaBrick extends Brick
{
    public static function getId(): string
    {
        return 'header_invoice_meta';
    }

    public static function getLabel(): string
    {
        return trans('ip.invoice_metadata');
    }

    public static function getIcon(): string | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>');
    }

    public static function getPreviewLabel(array $config): string
    {
        return trans('ip.invoice_metadata');
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.header-invoice-meta.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason.bricks.header-invoice-meta.index', [
            'config' => $config,
            'data' => $data,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_invoice_metadata'))
            ->modalHeading(trans('ip.invoice_metadata_settings'))
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'show_invoice_number' => $arguments['show_invoice_number'] ?? true,
                'show_invoice_date' => $arguments['show_invoice_date'] ?? true,
                'show_due_date' => $arguments['show_due_date'] ?? true,
                'show_po_number' => $arguments['show_po_number'] ?? false,
                'font_size' => $arguments['font_size'] ?? 10,
                'text_align' => $arguments['text_align'] ?? 'right',
            ])
            ->schema([
                Checkbox::make('show_invoice_number')
                    ->label(trans('ip.show_invoice_number'))
                    ->default(true),
                Checkbox::make('show_invoice_date')
                    ->label(trans('ip.show_invoice_date'))
                    ->default(true),
                Checkbox::make('show_due_date')
                    ->label(trans('ip.show_due_date'))
                    ->default(true),
                Checkbox::make('show_po_number')
                    ->label(trans('ip.show_po_number'))
                    ->default(false),
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(10)
                    ->minValue(8)
                    ->maxValue(16),
                Select::make('text_align')
                    ->label(trans('ip.text_align'))
                    ->options([
                        'left' => trans('ip.align_left'),
                        'center' => trans('ip.align_center'),
                        'right' => trans('ip.align_right'),
                    ])
                    ->default('right'),
            ])
            ->action(function (array $arguments, array $data, \Awcodes\Mason\Mason $component) {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return;
                }

                $brickContent = [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'config' => $data,
                        'id' => $arguments['id'],
                        'label' => $brick::getPreviewLabel($data),
                        'preview' => base64_encode($brick::toPreviewHtml($data)),
                    ],
                ];

                $component->runCommands([
                    \Awcodes\Mason\Actions\EditorCommand::make(
                        'insertContentAt',
                        arguments: [
                            $arguments['dragPosition'],
                            $brickContent,
                        ],
                    ),
                ]);
            });
    }
}
