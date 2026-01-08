<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Support\ExpenseCalculator;
use Modules\Products\Models\Product;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make()
                            ->schema([
                                Select::make('customer_id')
                                    ->relationship('customer', 'company_name')
                                    ->label(trans('ip.customer'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                Placeholder::make('customer_info')
                                    ->label(trans('ip.customer'))
                                    ->content(fn (Get $get) => optional($get('customer'))->company_name ?? '-')
                                    ->visible(fn (Get $get) => filled($get('customer_id'))),
                            ])
                            ->columnSpan(1),

                        Section::make()
                            ->schema([
                                Select::make('vendor_id')
                                    ->relationship('vendor', 'company_name')
                                    ->label(trans('ip.vendor'))
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                Placeholder::make('vendor_info')
                                    ->label(trans('ip.vendor'))
                                    ->content(fn (Get $get) => optional($get('vendor'))->company_name ?? '-')
                                    ->visible(fn (Get $get) => filled($get('vendor_id'))),
                            ])
                            ->columnSpan(1),

                        Section::make(trans('ip.details'))
                            ->schema([
                                TextInput::make('expense_number')
                                    ->disabled()
                                    ->required(),
                                Select::make('expense_status')
                                    ->options(collect(ExpenseStatus::cases())->mapWithKeys(fn ($s) => [$s->value => trans($s->label())])->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('category_id')
                                    ->relationship('expenseCategory', 'category_name')
                                    ->label(trans('ip.category'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('expense_type')
                                    ->options(collect(ExpenseType::cases())->mapWithKeys(fn ($t) => [$t->value => trans($t->label())])->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('expense_amount')
                                    ->numeric()
                                    ->required(),
                                DatePicker::make('expensed_at')
                                    ->required(),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make(trans('ip.expense_items'))
                    ->schema([
                        Repeater::make('expenseItems')
                            ->relationship('expenseItems')
                            ->label(trans('ip.expense_items'))
                            ->reorderable()
                            ->addActionLabel(trans('ip.add_row'))
                            ->columns(6) // Adjust columns to control field widths
                            ->schema([
                                Select::make('item_id')
                                    ->label(trans('ip.item'))
                                    ->options(Product::pluck('product_name', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('quantity')->numeric()->required(),
                                TextInput::make('price')->numeric()->required(),
                                TextInput::make('discount')->numeric()->default(0),
                                TextInput::make('subtotal')->numeric()->default(0)->disabled(),
                            ])
                            ->collapsed(false) // Optional: expand by default
                            ->afterStateUpdated(fn ($set, $get) => (new ExpenseCalculator())->updateGrandTotal($set, $get, 'expenseItems', 'subtotal', 'expense_item_subtotal')),
                    ])
                    ->columnSpanFull(),

                Section::make(trans('ip.expense_totals'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make()
                                    ->schema([]), // Left side: Empty (future use)

                                Group::make()
                                    ->schema([
                                        TextInput::make('expense_item_subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->disabled()
                                            ->dehydrated()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($set, $get) => (new ExpenseCalculator())->updateGrandTotal($set, $get, 'expenseItems', 'subtotal', 'expense_item_subtotal'))
                                            ->extraAttributes(['class' => 'text-right']),

                                        TextInput::make('expense_discount_amount')
                                            ->label(trans('ip.discount_amount'))
                                            ->nullable()
                                            ->extraAttributes(['class' => 'text-right']),

                                        TextInput::make('expense_discount_percent')
                                            ->label(trans('ip.discount_percent'))
                                            ->nullable()
                                            ->extraAttributes(['class' => 'text-right']),

                                        TextInput::make('expense_tax_total')
                                            ->label(trans('ip.tax_total'))
                                            ->disabled()
                                            ->extraAttributes(['class' => 'text-right']),

                                        TextInput::make('expense_total')
                                            ->label(trans('ip.total'))
                                            ->disabled()
                                            ->extraAttributes(['class' => 'text-right']),
                                    ]),
                            ]),
                    ])
                    ->collapsed(true)
                    ->columnSpanFull(),

                Section::make(trans('ip.expense_notes'))
                    ->schema([
                        MarkdownEditor::make('description')
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
