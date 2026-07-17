<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Filament\Company\Actions\InsertNoteTemplateAction;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Support\InvoiceCalculator;
use Modules\Products\Models\Product;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                // Top two-column: Client on left, Details on right
                //
                Grid::make(5)
                    ->columnSpanFull()
                    ->schema([
                        Schemas\Components\Group::make()
                            ->columnSpan(3)
                            ->schema([
                                Section::make(trans('ip.client'))
                                    ->schema([
                                        Select::make('customer_id')
                                            ->label(trans('ip.client'))
                                            ->relationship('customer', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('company_name')
                                                    ->label(trans('ip.customer_name'))
                                                    ->required(),
                                            ])
                                            ->reactive(),

                                        Placeholder::make('customer_info')
                                            ->label(trans('ip.client_information'))
                                            ->content(fn ($get) => optional($get('customer'))->company_name ?? '-'),
                                    ]),
                            ]),

                        Schemas\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make(trans('ip.details'))
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('invoice_number')
                                            ->label(trans('ip.invoice_number'))
                                            ->required(),

                                        Select::make('invoice_status')
                                            ->label(trans('ip.invoice_status'))
                                            ->options(
                                                collect(InvoiceStatus::cases())
                                                    ->mapWithKeys(fn ($s) => [$s->value => trans($s->label())])
                                                    ->toArray()
                                            )
                                            ->getOptionLabelUsing(
                                                fn ($value) => $value instanceof InvoiceStatus
                                                ? $value->label()
                                                : InvoiceStatus::tryFrom($value)?->label() ?? $value
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        DatePicker::make('invoiced_at')
                                            ->label(trans('ip.invoice_date'))
                                            ->required(),

                                        DatePicker::make('invoice_due_at')
                                            ->label(trans('ip.invoice_due_at'))
                                            ->required(),

                                        Select::make('numbering_id')
                                            ->label(trans('ip.numbering'))
                                            ->relationship('numbering', 'name', fn ($query) => $query->where('type', NumberingType::INVOICE->value))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->exists(
                                                table: 'numbering',
                                                column: 'id',
                                                modifyRuleUsing: fn ($rule) => $rule
                                                    ->where('type', NumberingType::INVOICE->value)
                                                    ->where('company_id', Filament::getTenant()?->id),
                                            ),

                                        TextInput::make('client_reference')
                                            ->label(trans('ip.client_reference'))
                                            ->maxLength(255),

                                        TextInput::make('work_order')
                                            ->label(trans('ip.work_order'))
                                            ->maxLength(255),

                                        TextInput::make('invoice_password')
                                            ->label(trans('ip.invoice_password')),
                                    ]),
                            ]),
                    ]),

                //
                // Items repeater (always visible, not collapsed)
                //
                // Invoice Items
                Section::make(trans('ip.invoice_items'))
                    ->collapsed()
                    ->schema([
                        Repeater::make('invoiceItems')
                            ->defaultItems(0)
                            ->relationship('invoiceItems')
                            ->label(trans('ip.invoice_items'))
                            ->reorderable()
                            ->addActionLabel(trans('ip.add_new_row'))
                            //->dehydrated()
                            ->schema([
                                Grid::make(6) // Adjust the number of columns as needed
                                    ->schema([
                                        Select::make('product_id')
                                            ->label(trans('ip.product'))
                                            ->options(Product::query()->pluck('product_name', 'id')->toArray())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->dehydrated(),

                                        TextEntry::make('product_name')
                                            ->state(fn ($get) => Product::query()->find($get('product_id'))?->product_name)
                                            ->disabled(),

                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->required()
                                            ->dehydrated(),

                                        TextInput::make('price')
                                            ->numeric()
                                            ->required()
                                            ->dehydrated(),

                                        TextInput::make('discount')
                                            ->numeric()
                                            ->default(0)
                                            ->dehydrated(),

                                        TextInput::make('subtotal')
                                            ->numeric()
                                            ->default(0)
                                            ->dehydrated()
                                            ->disabled(),
                                    ]),
                            ])
                            ->columns(1)
                            ->reactive()
                            /*->afterStateHydrated(function ($component, $state) {
                                // overwrite any stray default state with what the request provided
                                if (is_array($state) && $state !== []) {
                                    // Normalize to numeric keys so Livewire/Filament don’t try to merge by UUID
                                    $component->rawState(array_values($state));
                                }
                            })*/
                            ->afterStateUpdated(fn (callable $set, callable $get) => (new InvoiceCalculator())->updateGrandTotal($set, $get, 'invoiceItems', 'subtotal', 'invoice_item_subtotal')),
                    ])
                    ->columnSpanFull(),

                // Totals
                Section::make(trans('ip.invoice_amounts'))
                    ->columns(2)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // Left side reserved (e.g. “Add Item” button later)
                                Schemas\Components\Group::make()->schema([]),

                                // Right side: the actual totals
                                Schemas\Components\Group::make()
                                    ->schema([
                                        TextInput::make('invoice_item_subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->inlineLabel()
                                            ->disabled()
                                            ->dehydrated()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get): void {
                                                (new InvoiceCalculator())->updateGrandTotal($set, $get);
                                            }),

                                        TextInput::make('invoice_discount_amount')
                                            ->label(trans('ip.discount_amount'))
                                            ->inlineLabel()
                                            ->nullable(),

                                        TextInput::make('invoice_discount_percent')
                                            ->label(trans('ip.discount_percent'))
                                            ->inlineLabel()
                                            ->nullable(),

                                        TextInput::make('invoice_tax_total')
                                            ->label(trans('ip.tax_total'))
                                            ->inlineLabel()
                                            ->disabled(),

                                        TextInput::make('invoice_total')
                                            ->label(trans('ip.invoice_total'))
                                            ->inlineLabel()
                                            ->disabled(),
                                    ]),
                            ]),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),

                // Notes & Attachments
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.notes'))
                            ->collapsed()
                            ->schema([
                                MarkdownEditor::make('notes')
                                    ->label(trans('ip.notes'))
                                    ->toolbarButtons(['bold', 'italic'])
                                    ->hintAction(InsertNoteTemplateAction::make('notes')),
                            ])
                            ->columnSpan(1),

                        Section::make(trans('ip.attachments'))
                            ->collapsed()
                            ->schema([
                                FileUpload::make('attachments')
                                    ->label(trans('ip.attachments'))
                                    ->multiple(),
                            ])
                            ->columnSpan(1),
                    ]),

                // Invoice Terms
                Section::make(trans('ip.invoice_terms'))
                    ->collapsed()
                    ->schema([
                        MarkdownEditor::make('invoice_terms')
                            ->toolbarButtons(['bold', 'italic'])
                            ->label(trans('ip.invoice_terms'))
                            ->hintAction(InsertNoteTemplateAction::make('invoice_terms')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
